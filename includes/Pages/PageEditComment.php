<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Comment;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\Security\SecurityConfiguration;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageEditComment extends InternalPageBase
{
    /**
     * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
     * the return value from this function.
     *
     * If this page even supports actions, you will need to check the route
     *
     * @return SecurityConfiguration
     * @category Security-Critical
     */
    protected function getSecurityConfiguration()
    {
        switch ($this->getRouteName()) {
            case 'editOthers':
                return $this->getSecurityManager()->configure()->asAdminPage();
            default:
                return $this->getSecurityManager()->configure()->asInternalPage();
        }
    }

    /**
     * Main function for this page, when no specific actions are called.
     * @throws ApplicationLogicException
     */
    protected function main()
    {
        $commentId = WebRequest::getInt('id');
        if ($commentId === null) {
            throw new ApplicationLogicException('Comment ID not specified');
        }

        $database = $this->getDatabase();

        /** @var Comment $comment */
        $comment = Comment::getById($commentId, $database);
        if ($comment === false) {
            throw new ApplicationLogicException('Comment not found');
        }

        $currentUser = User::getCurrent($database);
        if ($comment->getUser() !== $currentUser->getId() && !$this->barrierTest('editOthers')) {
            throw new AccessDeniedException();
        }

        /** @var Request $request */
        $request = Request::getById($comment->getRequest(), $database);

        if ($request === false) {
            throw new ApplicationLogicException('Request was not found.');
        }

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $newComment = WebRequest::postString('newcomment');
            $visibility = WebRequest::postString('visibility');

            if ($visibility !== 'user' && $visibility !== 'admin') {
                throw new ApplicationLogicException('Comment visibility is not valid');
            }

            // optimisticly lock from the load of the edit comment form
            $updateVersion = WebRequest::postInt('updateversion');
            $comment->setUpdateVersion($updateVersion);

            $comment->setComment($newComment);
            $comment->setVisibility($visibility);

            $comment->save();

            Logger::editComment($database, $comment, $request);
            $this->getNotificationHelper()->commentEdited($comment, $request);
            SessionAlert::success("Comment has been saved successfully");

            $this->redirect('viewRequest', null, array('id' => $comment->getRequest()));
        }
        else {
            $this->assignCSRFToken();
            $this->assign('comment', $comment);
            $this->assign('request', $request);
            $this->assign('user', User::getById($comment->getUser(), $database));
            $this->setTemplate('edit-comment.tpl');
        }
    }
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages;

use Exception;
use Waca\DataObjects\Comment;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageEditComment extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @throws ApplicationLogicException
     * @throws Exception
     */
    protected function main()
    {
        $commentId = WebRequest::getInt('id');
        if ($commentId === null) {
            throw new ApplicationLogicException('Comment ID not specified');
        }

        $database = $this->getDatabase();

        /** @var Comment|false $comment */
        $comment = Comment::getById($commentId, $database);
        if ($comment === false) {
            throw new ApplicationLogicException('Comment not found');
        }

        $currentUser = User::getCurrent($database);

        $this->checkCommentAccess($comment, $currentUser);

        /** @var Request|false $request */
        $request = Request::getById($comment->getRequest(), $database);

        if ($request === false) {
            throw new ApplicationLogicException('Request was not found.');
        }

        $canUnflag = $this->barrierTest('unflag', $currentUser, PageFlagComment::class);

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $newComment = WebRequest::postString('newcomment');
            $visibility = WebRequest::postString('visibility');
            $doUnflag = WebRequest::postBoolean('unflag');

            if ($newComment === null || $newComment === '') {
                throw new ApplicationLogicException('Comment cannot be empty!');
            }

            $commentDataUnchanged = $newComment === $comment->getComment()
                && ($comment->getVisibility() === 'requester' || $comment->getVisibility() === $visibility);
            $flagStatusUnchanged = (!$canUnflag || $comment->getFlagged() && !$doUnflag);

            if ($commentDataUnchanged && $flagStatusUnchanged) {
                // No change was made; redirect back.
                $this->redirectBack($comment->getRequest());

                return;
            }

            // optimistically lock from the load of the edit comment form
            $updateVersion = WebRequest::postInt('updateversion');

            // comment data has changed, update the object
            if (!$commentDataUnchanged) {
                $this->updateCommentData($comment, $visibility, $newComment);
            }

            if ($doUnflag && $canUnflag) {
                $comment->setFlagged(false);
            }

            $comment->setUpdateVersion($updateVersion);
            $comment->save();

            if (!$commentDataUnchanged) {
                Logger::editComment($database, $comment, $request);
                $this->getNotificationHelper()->commentEdited($comment, $request);
            }

            if ($doUnflag && $canUnflag) {
                Logger::unflaggedComment($database, $comment, $request->getDomain());
            }

            SessionAlert::success('Comment has been saved successfully');
            $this->redirectBack($comment->getRequest());
        }
        else {
            $this->assignCSRFToken();
            $this->assign('comment', $comment);
            $this->assign('request', $request);
            $this->assign('user', User::getById($comment->getUser(), $database));
            $this->assign('canUnflag', $canUnflag);
            $this->setTemplate('edit-comment.tpl');
        }
    }

    /**
     * @throws AccessDeniedException
     */
    private function checkCommentAccess(Comment $comment, User $currentUser): void
    {
        if ($comment->getUser() !== $currentUser->getId() && !$this->barrierTest('editOthers', $currentUser)) {
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        $restrictedVisibility = $comment->getFlagged()
            || $comment->getVisibility() === 'admin'
            || $comment->getVisibility() === 'checkuser';

        if ($restrictedVisibility && !$this->barrierTest('alwaysSeePrivateData', $currentUser, 'RequestData')) {
            // Restricted visibility comments can only be seen if the user has a request reserved.
            /** @var Request $request */
            $request = Request::getById($comment->getRequest(), $comment->getDatabase());

            if ($request->getReserved() !== $currentUser->getId()) {
                throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
            }
        }

        if ($comment->getVisibility() === 'admin'
            && !$this->barrierTest('seeRestrictedComments', $currentUser, 'RequestData')
            && $comment->getUser() !== $currentUser->getId()) {
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        if ($comment->getVisibility() === 'checkuser'
            && !$this->barrierTest('seeCheckuserComments', $currentUser, 'RequestData')
            && $comment->getUser() !== $currentUser->getId()) {
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }
    }

    /**
     * @throws ApplicationLogicException
     */
    private function updateCommentData(Comment $comment, ?string $visibility, string $newComment): void
    {
        if ($comment->getVisibility() !== 'requester') {
            if ($visibility !== 'user' && $visibility !== 'admin' && $visibility !== 'checkuser') {
                throw new ApplicationLogicException('Comment visibility is not valid');
            }

            $comment->setVisibility($visibility);
        }

        $comment->setComment($newComment);
        $comment->touchEdited();
    }

    private function redirectBack(int $requestId): void
    {
        $source = WebRequest::getString('from');

        if ($source == 'flagged') {
            $this->redirect('flaggedComments');
        }
        else {
            $this->redirect('viewRequest', null, array('id' => $requestId));
        }
    }
}

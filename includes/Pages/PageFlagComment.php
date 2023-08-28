<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Comment;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageFlagComment extends InternalPageBase
{
    /**
     * @inheritDoc
     */
    protected function main()
    {
        if (!WebRequest::wasPosted()) {
            throw new ApplicationLogicException('This page does not support GET methods.');
        }

        $this->validateCSRFToken();

        $flagState = WebRequest::postInt('flag');
        $commentId = WebRequest::postInt('comment');
        $updateVersion = WebRequest::postInt('updateversion');

        if ($flagState !== 0 && $flagState !== 1) {
            throw new ApplicationLogicException('Flag status not valid');
        }

        $database = $this->getDatabase();

        /** @var Comment|false $comment */
        $comment = Comment::getById($commentId, $database);
        if ($comment === false) {
            throw new ApplicationLogicException('Unknown comment');
        }

        $currentUser = User::getCurrent($database);

        if ($comment->getFlagged() && !$this->barrierTest('unflag', $currentUser)) {
            // user isn't allowed to unflag comments
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        /** @var Request $request */
        $request = Request::getById($comment->getRequest(), $database);

        if ($comment->getFlagged()
            && !$this->barrierTest('alwaysSeePrivateData', $currentUser, 'RequestData')
            && $request->getReserved() !== $currentUser->getId()
        ) {
            // can't unflag if you can't see it.
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        $comment->setFlagged($flagState == 1);
        $comment->setUpdateVersion($updateVersion);
        $comment->save();

        if ($flagState === 1) {
            Logger::flaggedComment($database, $comment, $request->getDomain());
        }
        else {
            Logger::unflaggedComment($database, $comment, $request->getDomain());
        }

        if (WebRequest::postString('return') == 'list') {
            $this->redirect('flaggedComments');
        }
        else {
            $this->redirect('viewRequest', null, ['id' => $comment->getRequest()]);
        }
    }
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Waca\DataObjects\Comment;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\RegexConstants;
use Waca\WebRequest;

class PageComment extends RequestActionBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->checkPosted();
        $database = $this->getDatabase();
        $request = $this->getRequest($database);

        $commentText = WebRequest::postString('comment');
        if ($commentText === false || $commentText == '') {
            $this->redirect('viewRequest', null, array('id' => $request->getId()));

            return;
        }

        //Look for and detect IPv4/IPv6 addresses in comment text, and warn the commenter.
        $ipv4Regex = '/\b' . RegexConstants::IPV4 . '\b/';
        $ipv6Regex = '/\b' . RegexConstants::IPV6 . '\b/';

        $overridePolicy = WebRequest::postBoolean('privpol-check-override');

        if ((preg_match($ipv4Regex, $commentText) || preg_match($ipv6Regex, $commentText)) && !$overridePolicy) {
            $this->assignCSRFToken();
            $this->assign("request", $request);
            $this->assign("comment", $commentText);
            $this->skipAlerts();
            $this->setTemplate("privpol-warning.tpl");

            return;
        }

        $visibility = WebRequest::postString('visibility') ?? 'user';
        if ($visibility !== 'user' && $visibility !== 'admin' && $visibility !== 'checkuser') {
            throw new ApplicationLogicException('Invalid comment visibility');
        }

        $comment = new Comment();
        $comment->setDatabase($database);

        $comment->setRequest($request->getId());
        $comment->setVisibility($visibility);
        $comment->setUser(User::getCurrent($database)->getId());
        $comment->setComment($commentText);

        $comment->save();

        $this->getNotificationHelper()->commentCreated($comment, $request);
        $this->redirect('viewRequest', null, array('id' => $request->getId()));
    }
}

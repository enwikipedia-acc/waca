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
use Waca\PdoDatabase;
use Waca\Security\RoleConfigurationBase;
use Waca\Tasks\InternalPageBase;

class PageListFlaggedComments extends InternalPageBase
{
    /**
     * @inheritDoc
     */
    protected function main()
    {
        $this->setHtmlTitle('Flagged comments');
        $this->setTemplate('flagged-comments.tpl');

        $database = $this->getDatabase();
        $this->assignCSRFToken();

        /** @var Comment[] $commentObjects */
        $commentObjects = Comment::getFlaggedComments($database, 1); // FIXME: domains
        $comments = [];

        $currentUser = User::getCurrent($database);

        $seeRestrictedComments = $this->barrierTest('seeRestrictedComments', $currentUser, 'RequestData');
        $seeCheckuserComments = $this->barrierTest('seeCheckuserComments', $currentUser, 'RequestData');
        $alwaysSeePrivateData = $this->barrierTest('alwaysSeePrivateData', $currentUser, 'RequestData');

        foreach ($commentObjects as $object) {
            $data = [
                'visibility'    => $object->getVisibility(),
                'hidden'        => false,
                'hiddenText'    => false,
            ];

            if (!$alwaysSeePrivateData) {
                // tl;dr: This is a stupid configuration, but let's account for it anyway.
                //
                // Flagged comments are treated as private data. If you don't have the privilege
                // RequestData::alwaysSeePrivateData, then we can't show you the content of the comments here.
                // This page is forced to degrade into basically a list of requests, seriously hampering the usefulness
                // of this page. Still, we need to handle the case where we have access to this page, but not access
                // to private data.
                // At the time of writing, this case does not exist in the current role configuration, but for the role
                // configuration to be free of assumptions, we need this code.

                /** @var Request $request */
                $request = Request::getById($object->getRequest(), $database);

                if ($request->getReserved() === $currentUser->getId()) {
                    $data['hiddenText'] = false;
                }
                else {
                    $data['hiddenText'] = true;
                }
            }

            if ($object->getVisibility() == 'requester' || $object->getVisibility() == 'user') {
                $data['hidden'] = false;
            }
            elseif ($object->getVisibility() == 'admin') {
                if ($seeRestrictedComments) {
                    $data['hidden'] = false;
                }
                else {
                    $data['hidden'] = true;
                }
            }
            elseif ($object->getVisibility() == 'checkuser') {
                if ($seeCheckuserComments) {
                    $data['hidden'] = false;
                }
                else {
                    $data['hidden'] = true;
                }
            }

            $this->copyCommentData($object, $data, $database);

            $comments[] = $data;
        }

        $this->assign('comments', $comments);
        $this->assign('seeRestrictedComments', $seeRestrictedComments);
        $this->assign('seeCheckuserComments', $seeCheckuserComments);

        $this->assign('editOthersComments', $this->barrierTest('editOthers', $currentUser, PageEditComment::class));
        $this->assign('editComments', $this->barrierTest(RoleConfigurationBase::MAIN, $currentUser, PageEditComment::class));
        $this->assign('canUnflag', $this->barrierTest('unflag', $currentUser, PageFlagComment::class) && $this->barrierTest(RoleConfigurationBase::MAIN, $currentUser, PageFlagComment::class));
    }

    private function copyCommentData(Comment $object, array &$data, PdoDatabase $database): void
    {
        if ($data['hidden']) {
            // All details hidden, so don't copy anything.
            return;
        }

        /** @var Request $request */
        $request = Request::getById($object->getRequest(), $database);

        if (!$data['hiddenText']) {
            // Comment text is hidden, but presence of the comment is visible.
            $data['comment'] = $object->getComment();
        }

        $data['id'] = $object->getId();
        $data['updateversion'] = $object->getUpdateVersion();
        $data['time'] = $object->getTime();
        $data['requestid'] = $object->getRequest();
        $data['request'] = $request->getName();
        $data['requeststatus'] = $request->getStatus();
        $data['userid'] = $object->getUser();
        $data['user'] = User::getById($object->getUser(), $database)->getUsername();
    }
}
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
use Waca\PdoDatabase;
use Waca\Security\RoleConfiguration;
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

        foreach ($commentObjects as $object) {
            $data = [
                'id'            => $object->getId(),
                'visibility'    => $object->getVisibility(),
                'updateversion' => $object->getUpdateVersion(),
                'hidden'        => false
            ];

            if ($object->getVisibility() == 'requester' || $object->getVisibility() == 'user') {
                $this->copyCommentData($object, $data, $database);
            }
            elseif ($object->getVisibility() == 'admin') {
                if ($seeRestrictedComments) {
                    $this->copyCommentData($object, $data, $database);
                }
                else {
                    $data['hidden'] = true;
                }
            }
            elseif ($object->getVisibility() == 'checkuser') {
                if ($seeCheckuserComments) {
                    $this->copyCommentData($object, $data, $database);
                }
                else {
                    $data['hidden'] = true;
                }
            }

            $comments[] = $data;
        }

        $this->assign('comments', $comments);
        $this->assign('seeRestrictedComments', $seeRestrictedComments);
        $this->assign('seeCheckuserComments', $seeCheckuserComments);

        $this->assign('editOthersComments', $this->barrierTest('editOthers', $currentUser, PageEditComment::class));
        $this->assign('editComments', $this->barrierTest(RoleConfiguration::MAIN, $currentUser, PageEditComment::class));
        $this->assign('canUnflag', $this->barrierTest('unflag', $currentUser, PageFlagComment::class) && $this->barrierTest(RoleConfiguration::MAIN, $currentUser, PageFlagComment::class));
    }

    protected function copyCommentData(Comment $object, array &$data, PdoDatabase $database): void
    {
        $request = Request::getById($object->getRequest(), $database);

        $data['comment'] = $object->getComment();
        $data['time'] = $object->getTime();
        $data['requestid'] = $object->getRequest();
        $data['request'] = $request->getName();
        $data['requeststatus'] = $request->getStatus();
        $data['userid'] = $object->getUser();
        $data['user'] = User::getById($object->getUser(), $database)->getUsername();
    }
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Exception;
use PDO;
use Waca\DataObject;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Comment;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\DataObjects\WelcomeTemplate;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\PdoDatabase;
use Waca\Security\SecurityManager;
use Waca\SiteConfiguration;

class LogHelper
{
    /**
     * Summary of getRequestLogsWithComments
     *
     * @param int             $requestId
     * @param PdoDatabase     $db
     * @param SecurityManager $securityManager
     *
     * @return \Waca\DataObject[]
     */
    public static function getRequestLogsWithComments($requestId, PdoDatabase $db, SecurityManager $securityManager)
    {
        $logs = LogSearchHelper::get($db)->byObjectType('Request')->byObjectId($requestId)->fetch();

        $currentUser = User::getCurrent($db);
        $securityResult = $securityManager->allows('RequestData', 'seeRestrictedComments', $currentUser);
        $showAllComments = $securityResult === SecurityManager::ALLOWED;

        $comments = Comment::getForRequest($requestId, $db, $showAllComments, $currentUser->getId());

        $items = array_merge($logs, $comments);

        /**
         * @param DataObject $item
         *
         * @return int
         */
        $sortKey = function(DataObject $item) {
            if ($item instanceof Log) {
                return $item->getTimestamp()->getTimestamp();
            }

            if ($item instanceof Comment) {
                return $item->getTime()->getTimestamp();
            }

            return 0;
        };

        do {
            $flag = false;

            $loopLimit = (count($items) - 1);
            for ($i = 0; $i < $loopLimit; $i++) {
                // are these two items out of order?
                if ($sortKey($items[$i]) > $sortKey($items[$i + 1])) {
                    // swap them
                    $swap = $items[$i];
                    $items[$i] = $items[$i + 1];
                    $items[$i + 1] = $swap;

                    // set a flag to say we've modified the array this time around
                    $flag = true;
                }
            }
        }
        while ($flag);

        return $items;
    }

    /**
     * Summary of getLogDescription
     *
     * @param Log $entry
     *
     * @return string
     */
    public static function getLogDescription(Log $entry)
    {
        $text = "Deferred to ";
        if (substr($entry->getAction(), 0, strlen($text)) == $text) {
            // Deferred to a different queue
            // This is exactly what we want to display.
            return $entry->getAction();
        }

        $text = "Closed custom-n";
        if ($entry->getAction() == $text) {
            // Custom-closed
            return "closed (custom reason - account not created)";
        }

        $text = "Closed custom-y";
        if ($entry->getAction() == $text) {
            // Custom-closed
            return "closed (custom reason - account created)";
        }

        $text = "Closed 0";
        if ($entry->getAction() == $text) {
            // Dropped the request - short-circuit the lookup
            return "dropped request";
        }

        $text = "Closed ";
        if (substr($entry->getAction(), 0, strlen($text)) == $text) {
            // Closed with a reason - do a lookup here.
            $id = substr($entry->getAction(), strlen($text));
            /** @var EmailTemplate $template */
            $template = EmailTemplate::getById((int)$id, $entry->getDatabase());

            if ($template != false) {
                return "closed (" . $template->getName() . ")";
            }
        }

        // Fall back to the basic stuff
        $lookup = array(
            'Reserved'        => 'reserved',
            'Email Confirmed' => 'email-confirmed',
            'Unreserved'      => 'unreserved',
            'Approved'        => 'approved',
            'Suspended'       => 'suspended',
            'RoleChange'      => 'changed roles',
            'Banned'          => 'banned',
            'Edited'          => 'edited interface message',
            'Declined'        => 'declined',
            'EditComment-c'   => 'edited a comment',
            'EditComment-r'   => 'edited a comment',
            'Unbanned'        => 'unbanned',
            'Promoted'        => 'promoted to tool admin',
            'BreakReserve'    => 'forcibly broke the reservation',
            'Prefchange'      => 'changed user preferences',
            'Renamed'         => 'renamed',
            'Demoted'         => 'demoted from tool admin',
            'ReceiveReserved' => 'received the reservation',
            'SendReserved'    => 'sent the reservation',
            'EditedEmail'     => 'edited email',
            'DeletedTemplate' => 'deleted template',
            'EditedTemplate'  => 'edited template',
            'CreatedEmail'    => 'created email',
            'CreatedTemplate' => 'created template',
            'SentMail'        => 'sent an email to the requestor',
            'Registered'      => 'registered a tool account',
        );

        if (array_key_exists($entry->getAction(), $lookup)) {
            return $lookup[$entry->getAction()];
        }

        // OK, I don't know what this is. Fall back to something sane.
        return "performed an unknown action ({$entry->getAction()})";
    }

    /**
     * @param PdoDatabase $database
     *
     * @return array
     */
    public static function getLogActions(PdoDatabase $database)
    {
        $lookup = array(
            'Reserved'        => 'reserved',
            'Email Confirmed' => 'email-confirmed',
            'Unreserved'      => 'unreserved',
            'Approved'        => 'approved',
            'Suspended'       => 'suspended',
            'RoleChange'      => 'changed roles',
            'Banned'          => 'banned',
            'Edited'          => 'edited interface message',
            'Declined'        => 'declined',
            'EditComment-c'   => 'edited a comment (by comment ID)',
            'EditComment-r'   => 'edited a comment (by request)',
            'Unbanned'        => 'unbanned',
            'Promoted'        => 'promoted to tool admin',
            'BreakReserve'    => 'forcibly broke the reservation',
            'Prefchange'      => 'changed user preferences',
            'Renamed'         => 'renamed',
            'Demoted'         => 'demoted from tool admin',
            'ReceiveReserved' => 'received the reservation',
            'SendReserved'    => 'sent the reservation',
            'EditedEmail'     => 'edited email',
            'DeletedTemplate' => 'deleted template',
            'EditedTemplate'  => 'edited template',
            'CreatedEmail'    => 'created email',
            'CreatedTemplate' => 'created template',
            'SentMail'        => 'sent an email to the requestor',
            'Registered'      => 'registered a tool account',
            'Closed 0'        => 'dropped request',
        );

        $statement = $database->query(<<<SQL
SELECT CONCAT('Closed ', id) AS k, CONCAT('closed (',name,')') AS v
FROM emailtemplate;
SQL
        );
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $lookup[$row['k']] = $row['v'];
        }

        return $lookup;
    }

    public static function getObjectTypes()
    {
        return array(
            'Ban'             => 'Ban',
            'Comment'         => 'Comment',
            'EmailTemplate'   => 'Email template',
            'Request'         => 'Request',
            'SiteNotice'      => 'Site notice',
            'User'            => 'User',
            'WelcomeTemplate' => 'Welcome template',
        );
    }

    /**
     * This returns a HTML
     *
     * @param string            $objectId
     * @param string            $objectType
     * @param PdoDatabase       $database
     * @param SiteConfiguration $configuration
     *
     * @return null|string
     * @category Security-Critical
     */
    private static function getObjectDescription(
        $objectId,
        $objectType,
        PdoDatabase $database,
        SiteConfiguration $configuration
    ) {
        if ($objectType == '') {
            return null;
        }

        $baseurl = $configuration->getBaseUrl();

        switch ($objectType) {
            case 'Ban':
                /** @var Ban $ban */
                $ban = Ban::getById($objectId, $database);

                if ($ban === false) {
                    return 'Ban #' . $objectId . "</a>";
                }

                return 'Ban #' . $objectId . " (" . htmlentities($ban->getTarget()) . ")</a>";
            case 'EmailTemplate':
                /** @var EmailTemplate $emailTemplate */
                $emailTemplate = EmailTemplate::getById($objectId, $database);
                $name = htmlentities($emailTemplate->getName(), ENT_COMPAT, 'UTF-8');

                return <<<HTML
<a href="{$baseurl}/internal.php/emailManagement/view?id={$objectId}">Email Template #{$objectId} ({$name})</a>
HTML;
            case 'SiteNotice':
                return "<a href=\"{$baseurl}/internal.php/siteNotice\">the site notice</a>";
            case 'Request':
                /** @var Request $request */
                $request = Request::getById($objectId, $database);
                $name = htmlentities($request->getName(), ENT_COMPAT, 'UTF-8');

                return <<<HTML
<a href="{$baseurl}/internal.php/viewRequest?id={$objectId}">Request #{$objectId} ({$name})</a>
HTML;
            case 'User':
                /** @var User $user */
                $user = User::getById($objectId, $database);
                $username = htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8');

                return "<a href=\"{$baseurl}/internal.php/statistics/users/detail?user={$objectId}\">{$username}</a>";
            case 'WelcomeTemplate':
                /** @var WelcomeTemplate $welcomeTemplate */
                $welcomeTemplate = WelcomeTemplate::getById($objectId, $database);

                // some old templates have been completely deleted and lost to the depths of time.
                if ($welcomeTemplate === false) {
                    return "Welcome template #{$objectId}";
                }
                else {
                    $userCode = htmlentities($welcomeTemplate->getUserCode(), ENT_COMPAT, 'UTF-8');

                    return "<a href=\"{$baseurl}/internal.php/welcomeTemplates/view?template={$objectId}\">{$userCode}</a>";
                }
            default:
                return '[' . $objectType . " " . $objectId . ']';
        }
    }

    /**
     * @param    Log[]          $logs
     * @param     PdoDatabase   $database
     * @param SiteConfiguration $configuration
     *
     * @return array
     * @throws Exception
     */
    public static function prepareLogsForTemplate($logs, PdoDatabase $database, SiteConfiguration $configuration)
    {
        $userIds = array();

        /** @var Log $logEntry */
        foreach ($logs as $logEntry) {
            if (!$logEntry instanceof Log) {
                // if this happens, we've done something wrong with passing back the log data.
                throw new Exception('Log entry is not an instance of a Log, this should never happen.');
            }

            $user = $logEntry->getUser();
            if ($user === -1) {
                continue;
            }

            if (!array_search($user, $userIds)) {
                $userIds[] = $user;
            }
        }

        $users = UserSearchHelper::get($database)->inIds($userIds)->fetchMap('username');
        $users[-1] = User::getCommunity()->getUsername();

        $logData = array();

        /** @var Log $logEntry */
        foreach ($logs as $logEntry) {
            $objectDescription = self::getObjectDescription($logEntry->getObjectId(), $logEntry->getObjectType(),
                $database, $configuration);

            switch ($logEntry->getAction()) {
                case 'Renamed':
                    $renameData = unserialize($logEntry->getComment());
                    $oldName = htmlentities($renameData['old'], ENT_COMPAT, 'UTF-8');
                    $newName = htmlentities($renameData['new'], ENT_COMPAT, 'UTF-8');
                    $comment = 'Renamed \'' . $oldName . '\' to \'' . $newName . '\'.';
                    break;
                case 'RoleChange':
                    $roleChangeData = unserialize($logEntry->getComment());

                    $removed = array();
                    foreach ($roleChangeData['removed'] as $r) {
                        $removed[] = htmlentities($r, ENT_COMPAT, 'UTF-8');
                    }

                    $added = array();
                    foreach ($roleChangeData['added'] as $r) {
                        $added[] = htmlentities($r, ENT_COMPAT, 'UTF-8');
                    }

                    $reason = htmlentities($roleChangeData['reason'], ENT_COMPAT, 'UTF-8');

                    $roleDelta = 'Removed [' . implode(', ', $removed) . '], Added [' . implode(', ', $added) . ']';
                    $comment = $roleDelta . ' with comment: ' . $reason;
                    break;
                default:
                    $comment = $logEntry->getComment();
                    break;
            }

            $logData[] = array(
                'timestamp'         => $logEntry->getTimestamp(),
                'userid'            => $logEntry->getUser(),
                'username'          => $users[$logEntry->getUser()],
                'description'       => self::getLogDescription($logEntry),
                'objectdescription' => $objectDescription,
                'comment'           => $comment,
            );
        }

        return array($users, $logData);
    }
}

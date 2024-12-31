<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers;

use Exception;
use PDO;
use Waca\DataObject;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Comment;
use Waca\DataObjects\Domain;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\RequestForm;
use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\DataObjects\WelcomeTemplate;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\PdoDatabase;
use Waca\Security\ISecurityManager;
use Waca\SiteConfiguration;

class LogHelper
{
    /**
     * @param int             $requestId
     *
     * @return DataObject[]
     */
    public static function getRequestLogsWithComments(
        $requestId,
        PdoDatabase $db,
        ISecurityManager $securityManager
    ): array {
        // FIXME: domains
        $logs = LogSearchHelper::get($db, 1)->byObjectType('Request')->byObjectId($requestId)->fetch();

        $currentUser = User::getCurrent($db);
        $showRestrictedComments = $securityManager->allows('RequestData', 'seeRestrictedComments', $currentUser) === ISecurityManager::ALLOWED;
        $showCheckuserComments = $securityManager->allows('RequestData', 'seeCheckuserComments', $currentUser) === ISecurityManager::ALLOWED;

        $comments = Comment::getForRequest($requestId, $db, $showRestrictedComments, $showCheckuserComments, $currentUser->getId());

        $items = array_merge($logs, $comments);

        $sortKey = function(DataObject $item): int {
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

    public static function getLogDescription(Log $entry): string
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
            /** @var EmailTemplate|false $template */
            $template = EmailTemplate::getById((int)$id, $entry->getDatabase());

            if ($template !== false) {
                return 'closed (' . $template->getName() . ')';
            }
        }

        // Fall back to the basic stuff
        $lookup = array(
            'Reserved'            => 'reserved',
            'Email Confirmed'     => 'email-confirmed',
            'Manually Confirmed'  => 'manually confirmed the request',
            'Unreserved'          => 'unreserved',
            'Approved'            => 'approved',
            'DeactivatedUser'     => 'deactivated user',
            'RoleChange'          => 'changed roles',
            'GlobalRoleChange'    => 'changed global roles',
            'RequestedReactivation' => 'requested reactivation',
            'Banned'              => 'banned',
            'Edited'              => 'edited interface message',
            'EditComment-c'       => 'edited a comment',
            'EditComment-r'       => 'edited a comment',
            'FlaggedComment'      => 'flagged a comment',
            'UnflaggedComment'    => 'unflagged a comment',
            'Unbanned'            => 'unbanned',
            'BanReplaced'         => 'replaced ban',
            'Promoted'            => 'promoted to tool admin',
            'BreakReserve'        => 'forcibly broke the reservation',
            'Prefchange'          => 'changed user preferences',
            'Renamed'             => 'renamed',
            'Demoted'             => 'demoted from tool admin',
            'ReceiveReserved'     => 'received the reservation',
            'SendReserved'        => 'sent the reservation',
            'EditedEmail'         => 'edited email',
            'DeletedTemplate'     => 'deleted template',
            'EditedTemplate'      => 'edited template',
            'CreatedEmail'        => 'created email',
            'CreatedTemplate'     => 'created template',
            'SentMail'            => 'sent an email to the requester',
            'Registered'          => 'registered a tool account',
            'JobIssue'            => 'ran a background job unsuccessfully',
            'JobCompleted'        => 'completed a background job',
            'JobAcknowledged'     => 'acknowledged a job failure',
            'JobRequeued'         => 'requeued a job for re-execution',
            'JobCancelled'        => 'cancelled execution of a job',
            'EnqueuedJobQueue'    => 'scheduled for creation',
            'Hospitalised'        => 'sent to the hospital',
            'QueueCreated'        => 'created a request queue',
            'QueueEdited'         => 'edited a request queue',
            'DomainCreated'       => 'created a domain',
            'DomainEdited'        => 'edited a domain',
            'RequestFormCreated'  => 'created a request form',
            'RequestFormEdited'   => 'edited a request form',
        );

        if (array_key_exists($entry->getAction(), $lookup)) {
            return $lookup[$entry->getAction()];
        }

        // OK, I don't know what this is. Fall back to something sane.
        return "performed an unknown action ({$entry->getAction()})";
    }

    public static function getLogActions(PdoDatabase $database): array
    {
        $lookup = array(
            "Requests" => [
                'Reserved'            => 'reserved',
                'Email Confirmed'     => 'email-confirmed',
                'Manually Confirmed'  => 'manually confirmed',
                'Unreserved'          => 'unreserved',
                'EditComment-c'       => 'edited a comment (by comment ID)',
                'EditComment-r'       => 'edited a comment (by request)',
                'FlaggedComment'      => 'flagged a comment',
                'UnflaggedComment'    => 'unflagged a comment',
                'BreakReserve'        => 'forcibly broke the reservation',
                'ReceiveReserved'     => 'received the reservation',
                'SendReserved'        => 'sent the reservation',
                'SentMail'            => 'sent an email to the requester',
                'Closed 0'            => 'dropped request',
                'Closed custom-y'     => 'closed (custom reason - account created)',
                'Closed custom-n'     => 'closed (custom reason - account not created)',
            ],
            'Users' => [
                'Approved'            => 'approved',
                'DeactivatedUser'     => 'deactivated user',
                'RoleChange'          => 'changed roles',
                'GlobalRoleChange'    => 'changed global roles',
                'Prefchange'          => 'changed user preferences',
                'Renamed'             => 'renamed',
                'Promoted'            => 'promoted to tool admin',
                'Demoted'             => 'demoted from tool admin',
                'Registered'          => 'registered a tool account',
                'RequestedReactivation' => 'requested reactivation',
            ],
            "Bans" => [
                'Banned'              => 'banned',
                'Unbanned'            => 'unbanned',
                'BanReplaced'         => 'replaced ban',
            ],
            "Site notice" => [
                'Edited'              => 'edited interface message',
            ],
            "Email close templates" => [
                'EditedEmail'         => 'edited email',
                'CreatedEmail'        => 'created email',
            ],
            "Welcome templates" => [
                'DeletedTemplate'     => 'deleted template',
                'EditedTemplate'      => 'edited template',
                'CreatedTemplate'     => 'created template',
            ],
            "Job queue" => [
                'JobIssue'            => 'ran a background job unsuccessfully',
                'JobCompleted'        => 'completed a background job',
                'JobAcknowledged'     => 'acknowledged a job failure',
                'JobRequeued'         => 'requeued a job for re-execution',
                'JobCancelled'        => 'cancelled execution of a job',
                'EnqueuedJobQueue'    => 'scheduled for creation',
                'Hospitalised'        => 'sent to the hospital',
            ],
            "Request queues" => [
                'QueueCreated'        => 'created a request queue',
                'QueueEdited'         => 'edited a request queue',
            ],
            "Domains" => [
                'DomainCreated'       => 'created a domain',
                'DomainEdited'        => 'edited a domain',
            ],
            "Request forms" => [
                'RequestFormCreated'        => 'created a request form',
                'RequestFormEdited'         => 'edited a request form',
            ],
        );

        $databaseDrivenLogKeys = $database->query(<<<SQL
SELECT CONCAT('Closed ', id) AS k, CONCAT('closed (',name,')') AS v FROM emailtemplate
UNION ALL
SELECT CONCAT('Deferred to ', logname) AS k, CONCAT('deferred to ', displayname) AS v FROM requestqueue;
SQL
        );
        foreach ($databaseDrivenLogKeys->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $lookup["Requests"][$row['k']] = $row['v'];
        }

        return $lookup;
    }

    public static function getObjectTypes(): array
    {
        return array(
            'Ban'             => 'Ban',
            'Comment'         => 'Comment',
            'EmailTemplate'   => 'Email template',
            'JobQueue'        => 'Job queue item',
            'Request'         => 'Request',
            'SiteNotice'      => 'Site notice',
            'User'            => 'User',
            'WelcomeTemplate' => 'Welcome template',
            'RequestQueue'    => 'Request queue',
            'Domain'          => 'Domain',
            'RequestForm'     => 'Request form'
        );
    }

    /**
     * This returns an HTML representation of the object
     *
     * @param int               $objectId
     * @param string            $objectType
     *
     * @category Security-Critical
     */
    private static function getObjectDescription(
        $objectId,
        $objectType,
        PdoDatabase $database,
        SiteConfiguration $configuration
    ): ?string {
        if ($objectType == '') {
            return null;
        }

        $baseurl = $configuration->getBaseUrl();

        switch ($objectType) {
            case 'Ban':
                /** @var Ban $ban */
                $ban = Ban::getById($objectId, $database);

                if ($ban === false) {
                    return 'Ban #' . $objectId;
                }

                return <<<HTML
<a href="{$baseurl}/internal.php/bans/show?id={$objectId}">Ban #{$objectId}</a>
HTML;
            case 'EmailTemplate':
                /** @var EmailTemplate $emailTemplate */
                $emailTemplate = EmailTemplate::getById($objectId, $database);

                if ($emailTemplate === false) {
                    return 'Email Template #' . $objectId;
                }

                $name = htmlentities($emailTemplate->getName(), ENT_COMPAT, 'UTF-8');

                return <<<HTML
<a href="{$baseurl}/internal.php/emailManagement/view?id={$objectId}">Email Template #{$objectId} ({$name})</a>
HTML;
            case 'SiteNotice':
                return "<a href=\"{$baseurl}/internal.php/siteNotice\">the site notice</a>";
            case 'Request':
                /** @var Request $request */
                $request = Request::getById($objectId, $database);

                if ($request === false) {
                    return 'Request #' . $objectId;
                }

                $name = htmlentities($request->getName(), ENT_COMPAT, 'UTF-8');

                return <<<HTML
<a href="{$baseurl}/internal.php/viewRequest?id={$objectId}">Request #{$objectId} ({$name})</a>
HTML;
            case 'User':
                /** @var User $user */
                $user = User::getById($objectId, $database);

                // Some users were merged out of existence
                if ($user === false) {
                    return 'User #' . $objectId;
                }

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
            case 'JobQueue':
                /** @var JobQueue $job */
                $job = JobQueue::getById($objectId, $database);

                $taskDescriptions = JobQueue::getTaskDescriptions();

                if ($job === false) {
                    return 'Job Queue Task #' . $objectId;
                }

                $task = $job->getTask();
                if (isset($taskDescriptions[$task])) {
                    $description = $taskDescriptions[$task];
                }
                else {
                    $description = 'Unknown task';
                }

                return "<a href=\"{$baseurl}/internal.php/jobQueue/view?id={$objectId}\">Job #{$job->getId()} ({$description})</a>";
            case 'RequestQueue':
                /** @var RequestQueue $queue */
                $queue = RequestQueue::getById($objectId, $database);

                if ($queue === false) {
                    return "Request Queue #{$objectId}";
                }

                $queueHeader = htmlentities($queue->getHeader(), ENT_COMPAT, 'UTF-8');

                return "<a href=\"{$baseurl}/internal.php/queueManagement/edit?queue={$objectId}\">{$queueHeader}</a>";
            case 'Domain':
                /** @var Domain $domain */
                $domain = Domain::getById($objectId, $database);

                if ($domain === false) {
                    return "Domain #{$objectId}";
                }

                $domainName = htmlentities($domain->getShortName(), ENT_COMPAT, 'UTF-8');
                return "<a href=\"{$baseurl}/internal.php/domainManagement/edit?domain={$objectId}\">{$domainName}</a>";
            case 'RequestForm':
                /** @var RequestForm $queue */
                $queue = RequestForm::getById($objectId, $database);

                if ($queue === false) {
                    return "Request Form #{$objectId}";
                }

                $formName = htmlentities($queue->getName(), ENT_COMPAT, 'UTF-8');

                return "<a href=\"{$baseurl}/internal.php/requestFormManagement/edit?form={$objectId}\">{$formName}</a>";
            case 'Comment':
                /** @var Comment $comment */
                $comment = Comment::getById($objectId, $database);
                /** @var Request $request */
                $request = Request::getById($comment->getRequest(), $database);
                $requestName = htmlentities($request->getName(), ENT_COMPAT, 'UTF-8');

                return "<a href=\"{$baseurl}/internal.php/editComment?id={$objectId}\">Comment {$objectId}</a> on request <a href=\"{$baseurl}/internal.php/viewRequest?id={$comment->getRequest()}#comment-{$objectId}\">#{$comment->getRequest()} ({$requestName})</a>";
            default:
                return '[' . $objectType . " " . $objectId . ']';
        }
    }

    /**
     * @param Log[] $logs
     * @throws Exception
     *
     * @returns User[]
     */
    private static function loadUsersFromLogs(array $logs, PdoDatabase $database): array
    {
        $userIds = array();

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

        return $users;
    }

    /**
     * @param Log[] $logs
     *
     * @throws Exception
     */
    public static function prepareLogsForTemplate(
        array $logs,
        PdoDatabase $database,
        SiteConfiguration $configuration,
        ISecurityManager $securityManager
    ): array {
        $users = self::loadUsersFromLogs($logs, $database);
        $currentUser = User::getCurrent($database);

        $allowAccountLogSelf = $securityManager->allows('UserData', 'accountLogSelf', $currentUser) === ISecurityManager::ALLOWED;
        $allowAccountLog = $securityManager->allows('UserData', 'accountLog', $currentUser) === ISecurityManager::ALLOWED;

        $protectedLogActions = [
            'RequestedReactivation',
            'DeactivatedUser',
        ];

        $logData = array();
        foreach ($logs as $logEntry) {
            $objectDescription = self::getObjectDescription($logEntry->getObjectId(), $logEntry->getObjectType(),
                $database, $configuration);

            // initialise to sane default
            $comment = null;

            switch ($logEntry->getAction()) {
                case 'Renamed':
                    $renameData = unserialize($logEntry->getComment());
                    $oldName = htmlentities($renameData['old'], ENT_COMPAT, 'UTF-8');
                    $newName = htmlentities($renameData['new'], ENT_COMPAT, 'UTF-8');
                    $comment = 'Renamed \'' . $oldName . '\' to \'' . $newName . '\'.';
                    break;
                case 'RoleChange':
                case 'GlobalRoleChange':
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
                case 'JobIssue':
                    $jobIssueData = unserialize($logEntry->getComment());
                    $errorMessage = $jobIssueData['error'];
                    $status = $jobIssueData['status'];

                    $comment = 'Job ' . htmlentities($status, ENT_COMPAT, 'UTF-8') . ': ';
                    $comment .= htmlentities($errorMessage, ENT_COMPAT, 'UTF-8');
                    break;
                case 'JobIssueRequest':
                case 'JobCompletedRequest':
                    $jobData = unserialize($logEntry->getComment());

                    /** @var JobQueue $job */
                    $job = JobQueue::getById($jobData['job'], $database);
                    $descs = JobQueue::getTaskDescriptions();
                    $comment = htmlentities($descs[$job->getTask()], ENT_COMPAT, 'UTF-8');
                    break;

                case 'JobCompleted':
                    break;

                default:
                    $comment = $logEntry->getComment();
                    break;
            }

            if (in_array($logEntry->getAction(), $protectedLogActions) && $logEntry->getObjectType() === 'User') {
                if ($allowAccountLog) {
                    // do nothing, allowed to see all account logs
                }
                else if ($allowAccountLogSelf && $currentUser->getId() === $logEntry->getObjectId()) {
                    // do nothing, allowed to see own account log
                }
                else {
                    $comment = null;
                }
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

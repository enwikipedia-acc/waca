<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Exception;
use Waca\DataObject;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Comment;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\SiteNotice;
use Waca\DataObjects\User;
use Waca\DataObjects\WelcomeTemplate;
use Waca\PdoDatabase;

/**
 * Helper class for creating log entries
 *
 * Logger description.
 *
 * @version 1.0
 * @author  stwalkerster
 */
class Logger
{
    /**
     * @param PdoDatabase $database
     * @param Request     $object
     */
    public static function emailConfirmed(PdoDatabase $database, Request $object)
    {
        self::createLogEntry($database, $object, "Email Confirmed", null, User::getCommunity());
    }

    /**
     * @param PdoDatabase $database
     * @param DataObject  $object
     * @param string      $logAction
     * @param null|string $comment
     * @param User        $user
     *
     * @throws Exception
     */
    private static function createLogEntry(
        PdoDatabase $database,
        DataObject $object,
        $logAction,
        $comment = null,
        $user = null
    ) {
        if ($user == null) {
            $user = User::getCurrent($database);
        }

        $objectType = get_class($object);
        if (strpos($objectType, 'Waca\\DataObjects\\') !== false) {
            $objectType = str_replace('Waca\\DataObjects\\', '', $objectType);
        }

        $log = new Log();
        $log->setDatabase($database);
        $log->setAction($logAction);
        $log->setObjectId($object->getId());
        $log->setObjectType($objectType);
        $log->setUser($user);
        $log->setComment($comment);
        $log->save();
    }

    #region Users

    /**
     * @param PdoDatabase $database
     * @param User        $user
     */
    public static function newUser(PdoDatabase $database, User $user)
    {
        self::createLogEntry($database, $user, 'Registered', null, User::getCommunity());
    }

    /**
     * @param PdoDatabase $database
     * @param User        $object
     */
    public static function approvedUser(PdoDatabase $database, User $object)
    {
        self::createLogEntry($database, $object, "Approved");
    }

    /**
     * @param PdoDatabase $database
     * @param User        $object
     * @param string      $comment
     */
    public static function declinedUser(PdoDatabase $database, User $object, $comment)
    {
        self::createLogEntry($database, $object, "Declined", $comment);
    }

    /**
     * @param PdoDatabase $database
     * @param User        $object
     * @param string      $comment
     */
    public static function suspendedUser(PdoDatabase $database, User $object, $comment)
    {
        self::createLogEntry($database, $object, "Suspended", $comment);
    }

    /**
     * @param PdoDatabase $database
     * @param User        $object
     * @param string      $comment
     */
    public static function demotedUser(PdoDatabase $database, User $object, $comment)
    {
        self::createLogEntry($database, $object, "Demoted", $comment);
    }

    /**
     * @param PdoDatabase $database
     * @param User        $object
     */
    public static function promotedUser(PdoDatabase $database, User $object)
    {
        self::createLogEntry($database, $object, "Promoted");
    }

    /**
     * @param PdoDatabase $database
     * @param User        $object
     * @param string      $comment
     */
    public static function renamedUser(PdoDatabase $database, User $object, $comment)
    {
        self::createLogEntry($database, $object, "Renamed", $comment);
    }

    /**
     * @param PdoDatabase $database
     * @param User        $object
     */
    public static function userPreferencesChange(PdoDatabase $database, User $object)
    {
        self::createLogEntry($database, $object, "Prefchange");
    }

    /**
     * @param PdoDatabase $database
     * @param User        $object
     * @param string      $reason
     * @param array       $added
     * @param array       $removed
     */
    public static function userRolesEdited(PdoDatabase $database, User $object, $reason, $added, $removed)
    {
        $logData = serialize(array(
            'added'   => $added,
            'removed' => $removed,
            'reason'  => $reason,
        ));

        self::createLogEntry($database, $object, "RoleChange", $logData);
    }

    #endregion

    /**
     * @param PdoDatabase $database
     * @param SiteNotice  $object
     */
    public static function siteNoticeEdited(PdoDatabase $database, SiteNotice $object)
    {
        self::createLogEntry($database, $object, "Edited");
    }

    #region Welcome Templates

    /**
     * @param PdoDatabase     $database
     * @param WelcomeTemplate $object
     */
    public static function welcomeTemplateCreated(PdoDatabase $database, WelcomeTemplate $object)
    {
        self::createLogEntry($database, $object, "CreatedTemplate");
    }

    /**
     * @param PdoDatabase     $database
     * @param WelcomeTemplate $object
     */
    public static function welcomeTemplateEdited(PdoDatabase $database, WelcomeTemplate $object)
    {
        self::createLogEntry($database, $object, "EditedTemplate");
    }

    /**
     * @param PdoDatabase     $database
     * @param WelcomeTemplate $object
     */
    public static function welcomeTemplateDeleted(PdoDatabase $database, WelcomeTemplate $object)
    {
        self::createLogEntry($database, $object, "DeletedTemplate");
    }

    #endregion

    #region Bans

    /**
     * @param PdoDatabase $database
     * @param Ban         $object
     * @param string      $reason
     */
    public static function banned(PdoDatabase $database, Ban $object, $reason)
    {
        self::createLogEntry($database, $object, "Banned", $reason);
    }

    /**
     * @param PdoDatabase $database
     * @param Ban         $object
     * @param string      $reason
     */
    public static function unbanned(PdoDatabase $database, Ban $object, $reason)
    {
        self::createLogEntry($database, $object, "Unbanned", $reason);
    }

    #endregion

    #region Requests

    /**
     * @param PdoDatabase $database
     * @param Request     $object
     * @param string      $target
     */
    public static function deferRequest(PdoDatabase $database, Request $object, $target)
    {
        self::createLogEntry($database, $object, "Deferred to $target");
    }

    /**
     * @param PdoDatabase $database
     * @param Request     $object
     * @param integer     $target
     * @param string      $comment
     * @param User|null   $logUser
     */
    public static function closeRequest(PdoDatabase $database, Request $object, $target, $comment, User $logUser = null)
    {
        self::createLogEntry($database, $object, "Closed $target", $comment, $logUser);
    }

    /**
     * @param PdoDatabase $database
     * @param Request     $object
     */
    public static function reserve(PdoDatabase $database, Request $object)
    {
        self::createLogEntry($database, $object, "Reserved");
    }

    /**
     * @param PdoDatabase $database
     * @param Request     $object
     */
    public static function breakReserve(PdoDatabase $database, Request $object)
    {
        self::createLogEntry($database, $object, "BreakReserve");
    }

    /**
     * @param PdoDatabase $database
     * @param Request     $object
     */
    public static function unreserve(PdoDatabase $database, Request $object)
    {
        self::createLogEntry($database, $object, "Unreserved");
    }

    /**
     * @param PdoDatabase $database
     * @param Comment     $object
     * @param Request     $request
     */
    public static function editComment(PdoDatabase $database, Comment $object, Request $request)
    {
        self::createLogEntry($database, $request, "EditComment-r");
        self::createLogEntry($database, $object, "EditComment-c");
    }

    /**
     * @param PdoDatabase $database
     * @param Request     $object
     * @param User        $target
     */
    public static function sendReservation(PdoDatabase $database, Request $object, User $target)
    {
        self::createLogEntry($database, $object, "SendReserved");
        self::createLogEntry($database, $object, "ReceiveReserved", null, $target);
    }

    /**
     * @param PdoDatabase $database
     * @param Request     $object
     * @param string      $comment
     */
    public static function sentMail(PdoDatabase $database, Request $object, $comment)
    {
        self::createLogEntry($database, $object, "SentMail", $comment);
    }

    /**
     * @param PdoDatabase $database
     * @param Request     $object
     */
    public static function enqueuedJobQueue(PdoDatabase $database, Request $object)
    {
        self::createLogEntry($database, $object, 'EnqueuedJobQueue');
    }

    public static function hospitalised(PdoDatabase $database, Request $object)
    {
        self::createLogEntry($database, $object, 'Hospitalised');
    }
    #endregion

    #region Email templates

    /**
     * @param PdoDatabase   $database
     * @param EmailTemplate $object
     */
    public static function createEmail(PdoDatabase $database, EmailTemplate $object)
    {
        self::createLogEntry($database, $object, "CreatedEmail");
    }

    /**
     * @param PdoDatabase   $database
     * @param EmailTemplate $object
     */
    public static function editedEmail(PdoDatabase $database, EmailTemplate $object)
    {
        self::createLogEntry($database, $object, "EditedEmail");
    }

    #endregion

    #region Display

    #endregion

    #region Automation

    public static function backgroundJobComplete(PdoDatabase $database, JobQueue $job)
    {
        self::createLogEntry($database, $job, 'JobCompleted', null, User::getCommunity());
    }

    public static function backgroundJobIssue(PdoDatabase $database, JobQueue $job)
    {
        $data = array('status' => $job->getStatus(), 'error' => $job->getError());
        self::createLogEntry($database, $job, 'JobIssue', serialize($data), User::getCommunity());
    }

    public static function backgroundJobCancelled(PdoDatabase $database, JobQueue $job)
    {
        self::createLogEntry($database, $job, 'JobCancelled', $job->getError());
    }

    public static function backgroundJobRequeued(PdoDatabase $database, JobQueue $job)
    {
        self::createLogEntry($database, $job, 'JobRequeued');
    }

    public static function backgroundJobAcknowledged(PdoDatabase $database, JobQueue $job, $comment = null)
    {
        self::createLogEntry($database, $job, 'JobAcknowledged', $comment);
    }
    #endregion
}

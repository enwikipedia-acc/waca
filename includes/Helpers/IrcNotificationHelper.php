<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Exception;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Comment;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Notification;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\DataObjects\WelcomeTemplate;
use Waca\ExceptionHandler;
use Waca\IrcColourCode;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

/**
 * Class IrcNotificationHelper
 * @package Waca\Helpers
 */
class IrcNotificationHelper
{
    /** @var PdoDatabase $notificationsDatabase */
    private $notificationsDatabase;
    /** @var PdoDatabase $primaryDatabase */
    private $primaryDatabase;
    /** @var bool $notificationsEnabled */
    private $notificationsEnabled;
    /** @var int $notificationType */
    private $notificationType;
    /** @var User $currentUser */
    private $currentUser;
    /** @var string $instanceName */
    private $instanceName;
    /** @var string */
    private $baseUrl;
    /** @var array */
    private $requestStates;
    /** @var SiteConfiguration */
    private $siteConfiguration;

    /**
     * IrcNotificationHelper constructor.
     *
     * @param SiteConfiguration $siteConfiguration
     * @param PdoDatabase       $primaryDatabase
     * @param PdoDatabase|null  $notificationsDatabase
     */
    public function __construct(
        SiteConfiguration $siteConfiguration,
        PdoDatabase $primaryDatabase,
        ?PdoDatabase $notificationsDatabase = null
    ) {
        $this->primaryDatabase = $primaryDatabase;
        $this->siteConfiguration = $siteConfiguration;

        if ($notificationsDatabase !== null) {
            $this->notificationsDatabase = $notificationsDatabase;
            $this->notificationsEnabled = $siteConfiguration->getIrcNotificationsEnabled();
        }
        else {
            $this->notificationsEnabled = false;
        }

        $this->notificationType = $siteConfiguration->getIrcNotificationType();
        $this->instanceName = $siteConfiguration->getIrcNotificationsInstance();
        $this->baseUrl = $siteConfiguration->getBaseUrl();
        $this->requestStates = $siteConfiguration->getRequestStates();

        $this->currentUser = User::getCurrent($primaryDatabase);
    }

    /**
     * Send a notification
     *
     * @param string $message The text to send
     */
    protected function send($message)
    {
        $instanceName = $this->instanceName;

        if (!$this->notificationsEnabled) {
            return;
        }

        $blacklist = array("DCC", "CCTP", "PRIVMSG");
        $message = str_replace($blacklist, "(IRC Blacklist)", $message); // Lets stop DCC etc

        $msg = IrcColourCode::RESET . IrcColourCode::BOLD . "[$instanceName]" . IrcColourCode::RESET . ": $message";

        try {
            $notification = new Notification();
            $notification->setDatabase($this->notificationsDatabase);
            $notification->setType($this->notificationType);
            $notification->setText($msg);

            $notification->save();
        }
        catch (Exception $ex) {
            // OK, so we failed to send the notification - that db might be down?
            // This is non-critical, so silently fail.
            ExceptionHandler::logExceptionToDisk($ex, $this->siteConfiguration);

            // Disable notifications for remainder of request.
            $this->notificationsEnabled = false;
        }
    }

    #region user management

    /**
     * send a new user notification
     *
     * @param User $user
     */
    public function userNew(User $user)
    {
        $this->send("New user: {$user->getUsername()}");
    }

    /**
     * send an approved notification
     *
     * @param User $user
     */
    public function userApproved(User $user)
    {
        $this->send("{$user->getUsername()} approved by " . $this->currentUser->getUsername());
    }

    /**
     * send a declined notification
     *
     * @param User   $user
     * @param string $reason the reason the user was declined
     */
    public function userDeclined(User $user, $reason)
    {
        $this->send("{$user->getUsername()} declined by " . $this->currentUser->getUsername() . " ($reason)");
    }

    /**
     * send a suspended notification
     *
     * @param User   $user
     * @param string $reason The reason the user has been suspended
     */
    public function userSuspended(User $user, $reason)
    {
        $this->send("{$user->getUsername()} suspended by " . $this->currentUser->getUsername() . " ($reason)");
    }

    /**
     * Send a preference change notification
     *
     * @param User $user
     */
    public function userPrefChange(User $user)
    {
        $this->send("{$user->getUsername()}'s preferences were changed by " . $this->currentUser->getUsername());
    }

    /**
     * Send a user renamed notification
     *
     * @param User   $user
     * @param string $old
     */
    public function userRenamed(User $user, $old)
    {
        $this->send($this->currentUser->getUsername() . " renamed $old to {$user->getUsername()}");
    }

    /**
     * @param User   $user
     * @param string $reason
     */
    public function userRolesEdited(User $user, $reason)
    {
        $currentUser = $this->currentUser->getUsername();
        $this->send("Active roles for {$user->getUsername()} changed by " . $currentUser . " ($reason)");
    }

    #endregion

    #region Site Notice

    /**
     * Summary of siteNoticeEdited
     */
    public function siteNoticeEdited()
    {
        $this->send("Site notice edited by " . $this->currentUser->getUsername());
    }
    #endregion

    #region Welcome Templates
    /**
     * Summary of welcomeTemplateCreated
     *
     * @param WelcomeTemplate $template
     */
    public function welcomeTemplateCreated(WelcomeTemplate $template)
    {
        $this->send("Welcome template {$template->getId()} created by " . $this->currentUser->getUsername());
    }

    /**
     * Summary of welcomeTemplateDeleted
     *
     * @param int $templateid
     */
    public function welcomeTemplateDeleted($templateid)
    {
        $this->send("Welcome template {$templateid} deleted by " . $this->currentUser->getUsername());
    }

    /**
     * Summary of welcomeTemplateEdited
     *
     * @param WelcomeTemplate $template
     */
    public function welcomeTemplateEdited(WelcomeTemplate $template)
    {
        $this->send("Welcome template {$template->getId()} edited by " . $this->currentUser->getUsername());
    }

    #endregion

    #region bans
    /**
     * Summary of banned
     *
     * @param Ban $ban
     */
    public function banned(Ban $ban)
    {
        if ($ban->getDuration() === null) {
            $duration = "indefinitely";
        }
        else {
            $duration = "until " . date("F j, Y, g:i a", $ban->getDuration());
        }

        $username = $this->currentUser->getUsername();

        if ($ban->getVisibility() == 'user') {
            $this->send("Ban {$ban->getId()} set by {$username} for '{$ban->getReason()}' {$duration}");
        }
        else {
            $this->send("Ban {$ban->getId()} set by {$username} {$duration}");
        }
    }

    /**
     * Summary of unbanned
     *
     * @param Ban    $ban
     * @param string $unbanReason
     */
    public function unbanned(Ban $ban, $unbanReason)
    {
        $this->send("Ban {$ban->getId()} unbanned by " . $this->currentUser
                ->getUsername() . " (" . $unbanReason . ")");
    }

    #endregion

    #region request management

    /**
     * Summary of requestReceived
     *
     * @param Request $request
     */
    public function requestReceived(Request $request)
    {
        $this->send(
            IrcColourCode::DARK_GREY . "[["
            . IrcColourCode::DARK_GREEN . "acc:"
            . IrcColourCode::ORANGE . $request->getId()
            . IrcColourCode::DARK_GREY . "]]"
            . IrcColourCode::RED . " N "
            . IrcColourCode::DARK_BLUE . $this->baseUrl . "/internal.php/viewRequest?id={$request->getId()} "
            . IrcColourCode::DARK_RED . "* "
            . IrcColourCode::DARK_GREEN . $request->getName()
            . IrcColourCode::DARK_RED . " * "
            . IrcColourCode::RESET
        );
    }

    /**
     * Summary of requestDeferred
     *
     * @param Request $request
     */
    public function requestDeferred(Request $request)
    {
        $availableRequestStates = $this->requestStates;

        $deferTo = $availableRequestStates[$request->getStatus()]['deferto'];
        $username = $this->currentUser->getUsername();

        $this->send("Request {$request->getId()} ({$request->getName()}) deferred to {$deferTo} by {$username}");
    }

    /**
     *
     * Summary of requestDeferredWithMail
     *
     * @param Request $request
     */
    public function requestDeferredWithMail(Request $request)
    {
        $availableRequestStates = $this->requestStates;

        $deferTo = $availableRequestStates[$request->getStatus()]['deferto'];
        $username = $this->currentUser->getUsername();
        $id = $request->getId();
        $name = $request->getName();

        $this->send("Request {$id} ({$name}) deferred to {$deferTo} with an email by {$username}");
    }

    /**
     * Summary of requestClosed
     *
     * @param Request $request
     * @param string  $closetype
     */
    public function requestClosed(Request $request, $closetype)
    {
        $username = $this->currentUser->getUsername();

        $this->send("Request {$request->getId()} ({$request->getName()}) closed ($closetype) by {$username}");
    }

    /**
     * Summary of requestClosed
     *
     * @param Request $request
     * @param string  $closetype
     */
    public function requestCloseQueued(Request $request, $closetype)
    {
        $username = $this->currentUser->getUsername();

        $this->send("Request {$request->getId()} ({$request->getName()}) queued for creation ($closetype) by {$username}");
    }

    /**
     * Summary of requestClosed
     *
     * @param Request $request
     * @param User    $triggerUser
     */
    public function requestCreationFailed(Request $request, User $triggerUser)
    {
        $this->send("Request {$request->getId()} ({$request->getName()}) failed auto-creation for {$triggerUser->getUsername()}, and was sent to the hospital queue.");
    }

    /**
     * @param Request $request
     * @param User    $triggerUser
     */
    public function requestWelcomeFailed(Request $request, User $triggerUser)
    {
        $this->send("Request {$request->getId()} ({$request->getName()}) failed welcome for {$triggerUser->getUsername()}.");
    }

    /**
     * Summary of sentMail
     *
     * @param Request $request
     */
    public function sentMail(Request $request)
    {
        $this->send($this->currentUser->getUsername()
            . " sent an email related to Request {$request->getId()} ({$request->getName()})");
    }

    #endregion

    #region reservations

    /**
     * Summary of requestReserved
     *
     * @param Request $request
     */
    public function requestReserved(Request $request)
    {
        $username = $this->currentUser->getUsername();

        $this->send("Request {$request->getId()} ({$request->getName()}) reserved by {$username}");
    }

    /**
     * Summary of requestReserveBroken
     *
     * @param Request $request
     */
    public function requestReserveBroken(Request $request)
    {
        $username = $this->currentUser->getUsername();

        $this->send("Reservation on request {$request->getId()} ({$request->getName()}) broken by {$username}");
    }

    /**
     * Summary of requestUnreserved
     *
     * @param Request $request
     */
    public function requestUnreserved(Request $request)
    {
        $this->send("Request {$request->getId()} ({$request->getName()}) is no longer being handled.");
    }

    /**
     * Summary of requestReservationSent
     *
     * @param Request $request
     * @param User    $target
     */
    public function requestReservationSent(Request $request, User $target)
    {
        $username = $this->currentUser->getUsername();

        $this->send(
            "Reservation of request {$request->getId()} ({$request->getName()}) sent to {$target->getUsername()} by "
            . $username);
    }

    #endregion

    #region comments

    /**
     * Summary of commentCreated
     *
     * @param Comment $comment
     * @param Request $request
     */
    public function commentCreated(Comment $comment, Request $request)
    {
        $username = $this->currentUser->getUsername();
        $visibility = ($comment->getVisibility() == "admin" ? "private " : "");

        $this->send("{$username} posted a {$visibility}comment on request {$request->getId()} ({$request->getName()})");
    }

    /**
     * Summary of commentEdited
     *
     * @param Comment $comment
     * @param Request $request
     */
    public function commentEdited(Comment $comment, Request $request)
    {
        $username = $this->currentUser->getUsername();

        $this->send(<<<TAG
Comment {$comment->getId()} on request {$request->getId()} ({$request->getName()}) edited by {$username}
TAG
        );
    }

    #endregion

    #region email management (close reasons)

    /**
     * Summary of emailCreated
     *
     * @param EmailTemplate $template
     */
    public function emailCreated(EmailTemplate $template)
    {
        $username = $this->currentUser->getUsername();
        $this->send("Email {$template->getId()} ({$template->getName()}) created by " . $username);
    }

    /**
     * Summary of emailEdited
     *
     * @param EmailTemplate $template
     */
    public function emailEdited(EmailTemplate $template)
    {
        $username = $this->currentUser->getUsername();
        $this->send("Email {$template->getId()} ({$template->getName()}) edited by " . $username);
    }
    #endregion
}

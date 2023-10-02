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
use DateTime;
use Waca\DataObjects\Comment;
use Waca\DataObjects\Domain;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Fragments\RequestData;
use Waca\Helpers\LogHelper;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\PreferenceManager;
use Waca\Pages\RequestAction\PageManuallyConfirm;
use Waca\PdoDatabase;
use Waca\Security\RoleConfigurationBase;
use Waca\RequestStatus;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageViewRequest extends InternalPageBase
{
    use RequestData;

    const STATUS_SYMBOL_OPEN = '&#927';
    const STATUS_SYMBOL_ACCEPTED = '&#x2611';
    const STATUS_SYMBOL_REJECTED = '&#x2612';

    /**
     * Main function for this page, when no specific actions are called.
     * @throws ApplicationLogicException
     */
    protected function main()
    {
        // set up csrf protection
        $this->assignCSRFToken();

        // get some useful objects
        $database = $this->getDatabase();
        $request = $this->getRequest($database, WebRequest::getInt('id'));
        $config = $this->getSiteConfiguration();
        $currentUser = User::getCurrent($database);

        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $this->getDatabase());
        $this->assign('mediawikiScriptPath', $domain->getWikiArticlePath());

        // Shows a page if the email is not confirmed.
        if ($request->getEmailConfirm() !== 'Confirmed') {
            // Show a banner if the user can manually confirm the request
            $viewConfirm = $this->barrierTest(RoleConfigurationBase::MAIN, $currentUser, PageManuallyConfirm::class);

            // If the request is purged, there's nothing to confirm!
            if ($request->getEmail() === $this->getSiteConfiguration()->getDataClearEmail()) {
                $viewConfirm = false;
            }

            // Render
            $this->setTemplate("view-request/not-confirmed.tpl");
            $this->assign("requestId", $request->getId());
            $this->assign("requestVersion", $request->getUpdateVersion());
            $this->assign('canViewConfirmButton', $viewConfirm);

            // Make sure to return, to prevent the leaking of other information.
            return;
        }

        $this->setupBasicData($request, $config);

        $this->setupUsernameData($request);

        $this->setupTitle($request);

        $this->setupReservationDetails($request->getReserved(), $database, $currentUser);
        $this->setupGeneralData($database);

        $this->assign('requestDataCleared', false);
        if ($request->getEmail() === $this->getSiteConfiguration()->getDataClearEmail()) {
            $this->assign('requestDataCleared', true);
        }

        $allowedPrivateData = $this->isAllowedPrivateData($request, $currentUser);

        $this->setupCreationTypes($currentUser);

        $this->setupLogData($request, $database, $allowedPrivateData);

        $this->addJs("/api.php?action=templates&targetVariable=templateconfirms");

        $this->assign('showRevealLink', false);
        if ($request->getReserved() === $currentUser->getId() ||
            $this->barrierTest('alwaysSeeHash', $currentUser, 'RequestData')
        ) {
            $this->assign('showRevealLink', true);
            $this->assign('revealHash', $request->getRevealHash());
        }

        $this->assign('canSeeRelatedRequests', false);
        if ($allowedPrivateData || $this->barrierTest('seeRelatedRequests', $currentUser, 'RequestData')) {
            $this->setupRelatedRequests($request, $config, $database);
        }

        $this->assign('canCreateLocalAccount', $this->barrierTest('createLocalAccount', $currentUser, 'RequestData'));

        $closureDate = $request->getClosureDate();
        $date = new DateTime();
        $date->modify("-7 days");
        if ($request->getStatus() == "Closed" && $closureDate < $date) {
            $this->assign('isOldRequest', true);
        }
        $this->assign('canResetOldRequest', $this->barrierTest('reopenOldRequest', $currentUser, 'RequestData'));
        $this->assign('canResetPurgedRequest', $this->barrierTest('reopenClearedRequest', $currentUser, 'RequestData'));

        $this->assign('requestEmailSent', $request->getEmailSent());

        if ($allowedPrivateData) {
            $this->setTemplate('view-request/main-with-data.tpl');
            $this->setupPrivateData($request, $config);
            $this->assign('canSetBan', $this->barrierTest('set', $currentUser, PageBan::class));
            $this->assign('canSeeCheckuserData', $this->barrierTest('seeUserAgentData', $currentUser, 'RequestData'));

            if ($this->barrierTest('seeUserAgentData', $currentUser, 'RequestData')) {
                $this->setTemplate('view-request/main-with-checkuser-data.tpl');
                $this->setupCheckUserData($request);
            }
        }
        else {
            $this->setTemplate('view-request/main.tpl');
        }
    }

    /**
     * @param Request $request
     */
    protected function setupTitle(Request $request)
    {
        $statusSymbol = self::STATUS_SYMBOL_OPEN;
        if ($request->getStatus() === RequestStatus::CLOSED) {
            if ($request->getWasCreated()) {
                $statusSymbol = self::STATUS_SYMBOL_ACCEPTED;
            }
            else {
                $statusSymbol = self::STATUS_SYMBOL_REJECTED;
            }
        }

        $this->setHtmlTitle($statusSymbol . ' #' . $request->getId());
    }

    /**
     * Sets up data unrelated to the request, such as the email template information
     *
     * @param PdoDatabase $database
     */
    protected function setupGeneralData(PdoDatabase $database)
    {
        $this->assign('createAccountReason', 'Requested account at [[WP:ACC]], request #');

        // FIXME: domains
        /** @var Domain $domain */
        $domain = Domain::getById(1, $database);
        $this->assign('defaultRequestState', RequestQueue::getDefaultQueue($database, 1)->getApiName());
        $this->assign('activeRequestQueues', RequestQueue::getEnabledQueues($database));

        /** @var EmailTemplate $createdTemplate */
        $createdTemplate = EmailTemplate::getById($domain->getDefaultClose(), $database);

        $this->assign('createdHasJsQuestion', $createdTemplate->getJsquestion() != '');
        $this->assign('createdId', $createdTemplate->getId());
        $this->assign('createdName', $createdTemplate->getName());

        $preferenceManager = PreferenceManager::getForCurrent($database);
        $skipJsAborts = $preferenceManager->getPreference(PreferenceManager::PREF_SKIP_JS_ABORT);
        $preferredCreationMode = (int)$preferenceManager->getPreference(PreferenceManager::PREF_CREATION_MODE);
        $this->assign('skipJsAborts', $skipJsAborts);
        $this->assign('preferredCreationMode', $preferredCreationMode);

        $createReasons = EmailTemplate::getActiveNonpreloadTemplates(
            EmailTemplate::ACTION_CREATED,
            $database,
            $domain->getId(),
            $domain->getDefaultClose());
        $this->assign("createReasons", $createReasons);

        $declineReasons = EmailTemplate::getActiveNonpreloadTemplates(
            EmailTemplate::ACTION_NOT_CREATED,
            $database,
            $domain->getId());
        $this->assign("declineReasons", $declineReasons);

        $allCreateReasons = EmailTemplate::getAllActiveTemplates(
            EmailTemplate::ACTION_CREATED,
            $database,
            $domain->getId());
        $this->assign("allCreateReasons", $allCreateReasons);

        $allDeclineReasons = EmailTemplate::getAllActiveTemplates(
            EmailTemplate::ACTION_NOT_CREATED,
            $database,
            $domain->getId());
        $this->assign("allDeclineReasons", $allDeclineReasons);

        $allOtherReasons = EmailTemplate::getAllActiveTemplates(
            false,
            $database,
            $domain->getId());
        $this->assign("allOtherReasons", $allOtherReasons);
    }

    private function setupLogData(Request $request, PdoDatabase $database, bool $allowedPrivateData)
    {
        $currentUser = User::getCurrent($database);

        $logs = LogHelper::getRequestLogsWithComments($request->getId(), $database, $this->getSecurityManager());
        $requestLogs = array();

        /** @var User[] $nameCache */
        $nameCache = array();

        $editableComments = $this->barrierTest('editOthers', $currentUser, PageEditComment::class);

        $canFlag = $this->barrierTest(RoleConfigurationBase::MAIN, $currentUser, PageFlagComment::class);
        $canUnflag = $this->barrierTest('unflag', $currentUser, PageFlagComment::class);

        /** @var Log|Comment $entry */
        foreach ($logs as $entry) {
            // both log and comment have a 'user' field
            if (!array_key_exists($entry->getUser(), $nameCache)) {
                $entryUser = User::getById($entry->getUser(), $database);
                $nameCache[$entry->getUser()] = $entryUser;
            }

            if ($entry instanceof Comment) {
                // Determine if the comment contains private information.
                // Private defined as flagged or restricted visibility, but only when the user isn't allowed
                // to see private data
                $commentIsRestricted =
                    ($entry->getFlagged()
                        || $entry->getVisibility() == 'admin' || $entry->getVisibility() == 'checkuser')
                    && !$allowedPrivateData;

                // Only allow comment editing if the user is able to edit comments or this is the user's own comment,
                // but only when they're allowed to see the comment itself.
                $commentIsEditable = ($editableComments || $entry->getUser() == $currentUser->getId())
                    && !$commentIsRestricted;

                // Flagging/unflagging can only be done if you can see the comment
                $canFlagThisComment = $canFlag
                    && (
                        (!$entry->getFlagged() && !$commentIsRestricted)
                        || ($entry->getFlagged() && $canUnflag && $commentIsEditable)
                    );

                $requestLogs[] = array(
                    'type'          => 'comment',
                    'security'      => $entry->getVisibility(),
                    'user'          => $entry->getVisibility() == 'requester' ? $request->getName() : $nameCache[$entry->getUser()]->getUsername(),
                    'userid'        => $entry->getUser() == -1 ? null : $entry->getUser(),
                    'entry'         => null,
                    'time'          => $entry->getTime(),
                    'canedit'       => $commentIsEditable,
                    'id'            => $entry->getId(),
                    'comment'       => $entry->getComment(),
                    'flagged'       => $entry->getFlagged(),
                    'canflag'       => $canFlagThisComment,
                    'updateversion' => $entry->getUpdateVersion(),
                    'edited'        => $entry->getEdited(),
                    'hidden'        => $commentIsRestricted
                );
            }

            if ($entry instanceof Log) {
                $invalidUserId = $entry->getUser() === -1 || $entry->getUser() === 0;
                $entryUser = $invalidUserId ? User::getCommunity() : $nameCache[$entry->getUser()];

                $entryComment = $entry->getComment();

                if ($entry->getAction() === 'JobIssueRequest' || $entry->getAction() === 'JobCompletedRequest') {
                    $data = unserialize($entry->getComment());
                    /** @var JobQueue $job */
                    $job = JobQueue::getById($data['job'], $database);
                    $requestLogs[] = array(
                        'type'     => 'joblog',
                        'security' => 'user',
                        'userid'   => $entry->getUser() == -1 ? null : $entry->getUser(),
                        'user'     => $entryUser->getUsername(),
                        'entry'    => LogHelper::getLogDescription($entry),
                        'time'     => $entry->getTimestamp(),
                        'canedit'  => false,
                        'id'       => $entry->getId(),
                        'jobId'    => $job->getId(),
                        'jobDesc'  => JobQueue::getTaskDescriptions()[$job->getTask()],
                    );
                }
                else {
                    $requestLogs[] = array(
                        'type'     => 'log',
                        'security' => 'user',
                        'userid'   => $entry->getUser() == -1 ? null : $entry->getUser(),
                        'user'     => $entryUser->getUsername(),
                        'entry'    => LogHelper::getLogDescription($entry),
                        'time'     => $entry->getTimestamp(),
                        'canedit'  => false,
                        'id'       => $entry->getId(),
                        'comment'  => $entryComment,
                    );
                }
            }
        }

        $this->addJs("/api.php?action=users&targetVariable=typeaheaddata");

        $this->assign("requestLogs", $requestLogs);
    }

    /**
     * @param Request $request
     */
    protected function setupUsernameData(Request $request)
    {
        $blacklistData = $this->getBlacklistHelper()->isBlacklisted($request->getName());

        $this->assign('requestIsBlacklisted', $blacklistData !== false);
        $this->assign('requestBlacklist', $blacklistData);

        try {
            $spoofs = $this->getAntiSpoofProvider()->getSpoofs($request->getName());
        }
        catch (Exception $ex) {
            $spoofs = $ex->getMessage();
        }

        $this->assign("spoofs", $spoofs);
    }

    private function setupCreationTypes(User $user)
    {
        $this->assign('allowWelcomeSkip', false);
        $this->assign('forceWelcomeSkip', false);

        $database = $this->getDatabase();
        $preferenceManager = PreferenceManager::getForCurrent($database);

        $oauth = new OAuthUserHelper($user, $database, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());

        $welcomeTemplate = $preferenceManager->getPreference(PreferenceManager::PREF_WELCOMETEMPLATE);

        if ($welcomeTemplate != null) {
            $this->assign('allowWelcomeSkip', true);

            if (!$oauth->canWelcome()) {
                $this->assign('forceWelcomeSkip', true);
            }
        }

        // test credentials
        $canManualCreate = $this->barrierTest(PreferenceManager::CREATION_MANUAL, $user, 'RequestCreation');
        $canOauthCreate = $this->barrierTest(PreferenceManager::CREATION_OAUTH, $user, 'RequestCreation');
        $canBotCreate = $this->barrierTest(PreferenceManager::CREATION_BOT, $user, 'RequestCreation');

        $this->assign('canManualCreate', $canManualCreate);
        $this->assign('canOauthCreate', $canOauthCreate);
        $this->assign('canBotCreate', $canBotCreate);

        // show/hide the type radio buttons
        $creationHasChoice = count(array_filter([$canManualCreate, $canOauthCreate, $canBotCreate])) > 1;

        $creationModePreference = $preferenceManager->getPreference(PreferenceManager::PREF_CREATION_MODE);
        if (!$this->barrierTest($creationModePreference, $user, 'RequestCreation')) {
            // user is not allowed to use their default. Force a choice.
            $creationHasChoice = true;
        }

        $this->assign('creationHasChoice', $creationHasChoice);

        // determine problems in creation types
        $this->assign('botProblem', false);
        if ($canBotCreate && $this->getSiteConfiguration()->getCreationBotPassword() === null) {
            $this->assign('botProblem', true);
        }

        $this->assign('oauthProblem', false);
        if ($canOauthCreate && !$oauth->canCreateAccount()) {
            $this->assign('oauthProblem', true);
        }
    }
}

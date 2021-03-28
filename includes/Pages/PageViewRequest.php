<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Exception;
use DateTime;
use Waca\DataObjects\Comment;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Fragments\RequestData;
use Waca\Helpers\LogHelper;
use Waca\Helpers\OAuthUserHelper;
use Waca\PdoDatabase;
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

        // Test we should be able to look at this request
        if ($config->getEmailConfirmationEnabled()) {
            if ($request->getEmailConfirm() !== 'Confirmed') {
                // Not allowed to look at this yet.
                throw new ApplicationLogicException('The email address has not yet been confirmed for this request.');
            }
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

        $this->setupLogData($request, $database);

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
        if ($request->getStatus() === 'Closed') {
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
        $config = $this->getSiteConfiguration();

        $this->assign('createAccountReason', 'Requested account at [[WP:ACC]], request #');

        $this->assign('defaultRequestState', $config->getDefaultRequestStateKey());

        $this->assign('requestStates', $config->getRequestStates());

        /** @var EmailTemplate $createdTemplate */
        $createdTemplate = EmailTemplate::getById($config->getDefaultCreatedTemplateId(), $database);

        $this->assign('createdHasJsQuestion', $createdTemplate->getJsquestion() != '');
        $this->assign('createdId', $createdTemplate->getId());
        $this->assign('createdName', $createdTemplate->getName());

        $createReasons = EmailTemplate::getActiveTemplates(EmailTemplate::CREATED, $database);
        $this->assign("createReasons", $createReasons);
        $declineReasons = EmailTemplate::getActiveTemplates(EmailTemplate::NOT_CREATED, $database);
        $this->assign("declineReasons", $declineReasons);

        $allCreateReasons = EmailTemplate::getAllActiveTemplates(EmailTemplate::CREATED, $database);
        $this->assign("allCreateReasons", $allCreateReasons);
        $allDeclineReasons = EmailTemplate::getAllActiveTemplates(EmailTemplate::NOT_CREATED, $database);
        $this->assign("allDeclineReasons", $allDeclineReasons);
        $allOtherReasons = EmailTemplate::getAllActiveTemplates(false, $database);
        $this->assign("allOtherReasons", $allOtherReasons);
    }

    private function setupLogData(Request $request, PdoDatabase $database)
    {
        $currentUser = User::getCurrent($database);

        $logs = LogHelper::getRequestLogsWithComments($request->getId(), $database, $this->getSecurityManager());
        $requestLogs = array();

        /** @var User[] $nameCache */
        $nameCache = array();

        $editableComments = $this->barrierTest('editOthers', $currentUser, PageEditComment::class);

        /** @var Log|Comment $entry */
        foreach ($logs as $entry) {
            // both log and comment have a 'user' field
            if (!array_key_exists($entry->getUser(), $nameCache)) {
                $entryUser = User::getById($entry->getUser(), $database);
                $nameCache[$entry->getUser()] = $entryUser;
            }

            if ($entry instanceof Comment) {
                $requestLogs[] = array(
                    'type'     => 'comment',
                    'security' => $entry->getVisibility(),
                    'user'     => $entry->getVisibility() == 'requester' ? $request->getName() : $nameCache[$entry->getUser()]->getUsername(),
                    'userid'   => $entry->getUser() == -1 ? null : $entry->getUser(),
                    'entry'    => null,
                    'time'     => $entry->getTime(),
                    'canedit'  => ($editableComments || $entry->getUser() == $currentUser->getId()),
                    'id'       => $entry->getId(),
                    'comment'  => $entry->getComment(),
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

        $oauth = new OAuthUserHelper($user, $this->getDatabase(), $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());

        if ($user->getWelcomeTemplate() != 0) {
            $this->assign('allowWelcomeSkip', true);

            if (!$oauth->canWelcome()) {
                $this->assign('forceWelcomeSkip', true);
            }
        }

        // test credentials
        $canManualCreate = $this->barrierTest(User::CREATION_MANUAL, $user, 'RequestCreation');
        $canOauthCreate = $this->barrierTest(User::CREATION_OAUTH, $user, 'RequestCreation');
        $canBotCreate = $this->barrierTest(User::CREATION_BOT, $user, 'RequestCreation');

        $this->assign('canManualCreate', $canManualCreate);
        $this->assign('canOauthCreate', $canOauthCreate);
        $this->assign('canBotCreate', $canBotCreate);

        // show/hide the type radio buttons
        $creationHasChoice = count(array_filter([$canManualCreate, $canOauthCreate, $canBotCreate])) > 1;

        if (!$this->barrierTest($user->getCreationMode(), $user, 'RequestCreation')) {
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

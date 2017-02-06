<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Exception;
use Waca\DataObjects\Comment;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Fragments\RequestData;
use Waca\Helpers\LogHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\PdoDatabase;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageViewRequest extends InternalPageBase
{
    use RequestData;
    const STATUS_SYMBOL_OPEN = '&#x2610';
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

        $this->setupLogData($request, $database);

        if ($allowedPrivateData) {
            $this->setTemplate('view-request/main-with-data.tpl');
            $this->setupPrivateData($request, $currentUser, $this->getSiteConfiguration(), $database);

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
        $this->assign('createdJsQuestion', $createdTemplate->getJsquestion());
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

        $this->getTypeAheadHelper()->defineTypeAheadSource('username-typeahead', function() use ($database) {
            return UserSearchHelper::get($database)->byStatus('Active')->fetchColumn('username');
        });
    }

    private function setupLogData(Request $request, PdoDatabase $database)
    {
        $currentUser = User::getCurrent($database);

        $logs = LogHelper::getRequestLogsWithComments($request->getId(), $database, $this->getSecurityManager());
        $requestLogs = array();

        if (trim($request->getComment()) !== "") {
            $requestLogs[] = array(
                'type'     => 'comment',
                'security' => 'user',
                'userid'   => null,
                'user'     => $request->getName(),
                'entry'    => null,
                'time'     => $request->getDate(),
                'canedit'  => false,
                'id'       => $request->getId(),
                'comment'  => $request->getComment(),
            );
        }

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
                    'user'     => $nameCache[$entry->getUser()]->getUsername(),
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

                $requestLogs[] = array(
                    'type'     => 'log',
                    'security' => 'user',
                    'userid'   => $entry->getUser() == -1 ? null : $entry->getUser(),
                    'user'     => $entryUser->getUsername(),
                    'entry'    => LogHelper::getLogDescription($entry),
                    'time'     => $entry->getTimestamp(),
                    'canedit'  => false,
                    'id'       => $entry->getId(),
                    'comment'  => $entry->getComment(),
                );
            }
        }

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
}

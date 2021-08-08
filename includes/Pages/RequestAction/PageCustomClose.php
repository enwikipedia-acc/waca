<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Exception;
use Waca\Background\Task\BotCreationTask;
use Waca\Background\Task\UserCreationTask;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Fragments\RequestData;
use Waca\Helpers\Logger;
use Waca\Helpers\OAuthUserHelper;
use Waca\PdoDatabase;
use Waca\RequestStatus;
use Waca\SessionAlert;
use Waca\WebRequest;

class PageCustomClose extends PageCloseRequest
{
    use RequestData;

    public const CREATE_OAUTH = 'created-oauth';
    public const CREATE_BOT = 'created-bot';

    protected function main()
    {
        $database = $this->getDatabase();

        $request = $this->getRequest($database);
        $currentUser = User::getCurrent($this->getDatabase());

        if ($request->getStatus() === 'Closed') {
            throw new ApplicationLogicException('Request is already closed');
        }

        // Dual-mode page
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $success = $this->doCustomClose($currentUser, $request, $database);

            if ($success) {
                $this->redirect();
            }
        }
        else {
            $this->assignCSRFToken();
            $this->showCustomCloseForm($database, $request);
        }
    }

    /**
     * @param $database
     *
     * @return Request
     * @throws ApplicationLogicException
     */
    protected function getRequest(PdoDatabase $database)
    {
        $requestId = WebRequest::getInt('request');
        if ($requestId === null) {
            throw new ApplicationLogicException('Request ID not found');
        }

        /** @var Request $request */
        $request = Request::getById($requestId, $database);

        if ($request === false) {
            throw new ApplicationLogicException('Request not found');
        }

        return $request;
    }

    /**
     * @param PdoDatabase $database
     *
     * @return EmailTemplate|null
     */
    protected function getTemplate(PdoDatabase $database)
    {
        $templateId = WebRequest::getInt('template');
        if ($templateId === null) {
            return null;
        }

        /** @var EmailTemplate $template */
        $template = EmailTemplate::getById($templateId, $database);
        if ($template === false || !$template->getActive()) {
            return null;
        }

        return $template;
    }

    /**
     * @param $database
     * @param $request
     *
     * @throws Exception
     */
    protected function showCustomCloseForm(PdoDatabase $database, Request $request)
    {
        $this->setHtmlTitle("Custom close");

        $currentUser = User::getCurrent($database);
        $config = $this->getSiteConfiguration();

        $allowedPrivateData = $this->isAllowedPrivateData($request, $currentUser);
        if (!$allowedPrivateData) {
            // we probably shouldn't be showing the user this form if they're not allowed to access private data...
            throw new AccessDeniedException($this->getSecurityManager());
        }

        $template = $this->getTemplate($database);

        // Preload data
        $this->assign('defaultAction', '');
        $this->assign('preloadText', '');
        $this->assign('preloadTitle', '');

        if ($template !== null) {
            $this->assign('defaultAction', $template->getDefaultAction());
            $this->assign('preloadText', $template->getText());
            $this->assign('preloadTitle', $template->getName());
        }

        // Static data
        $this->assign('requeststates', $config->getRequestStates());

        // request data
        $this->assign('requestId', $request->getIp());
        $this->assign('updateVersion', $request->getUpdateVersion());
        $this->setupBasicData($request, $config);
        $this->setupReservationDetails($request->getReserved(), $database, $currentUser);
        $this->setupPrivateData($request, $config);
        $this->setupRelatedRequests($request, $config, $database);

        // IP location
        $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp($request->getIp(), $request->getForwardedIp());
        $this->assign('iplocation', $this->getLocationProvider()->getIpLocation($trustedIp));

        // Confirmations
        $this->assign('confirmEmailAlreadySent', $this->checkEmailAlreadySent($request));

        $this->assign('canSkipCcMailingList', $this->barrierTest('skipCcMailingList', $currentUser));

        $this->assign('allowWelcomeSkip', false);
        $this->assign('forceWelcomeSkip', false);

        $canOauthCreate = $this->barrierTest(User::CREATION_OAUTH, $currentUser, 'RequestCreation');
        $canBotCreate = $this->barrierTest(User::CREATION_BOT, $currentUser, 'RequestCreation');

        $oauth = new OAuthUserHelper($currentUser, $this->getDatabase(), $this->getOAuthProtocolHelper(), $config);

        if ($currentUser->getWelcomeTemplate() != 0) {
            $this->assign('allowWelcomeSkip', true);

            if (!$oauth->canWelcome()) {
                $this->assign('forceWelcomeSkip', true);
            }
        }

        // disable options if there's a misconfiguration.
        $canOauthCreate &= $oauth->canCreateAccount();
        $canBotCreate &= $this->getSiteConfiguration()->getCreationBotPassword() !== null;

        $this->assign('canOauthCreate', $canOauthCreate);
        $this->assign('canBotCreate', $canBotCreate);

        // template
        $this->setTemplate('custom-close.tpl');
    }

    /**
     * @param User        $currentUser
     * @param Request     $request
     * @param PdoDatabase $database
     *
     * @throws ApplicationLogicException
     */
    protected function doCustomClose(User $currentUser, Request $request, PdoDatabase $database) : bool
    {
        $messageBody = WebRequest::postString('msgbody');
        if ($messageBody === null || trim($messageBody) === '') {
            throw new ApplicationLogicException('Message body cannot be blank');
        }

        $ccMailingList = true;
        if ($this->barrierTest('skipCcMailingList', $currentUser)) {
            $ccMailingList = WebRequest::postBoolean('ccMailingList');
        }

        if ($request->getStatus() === 'Closed') {
            throw new ApplicationLogicException('Request is already closed');
        }

        if (!(WebRequest::postBoolean('confirmEmailAlreadySent'))
        ) {
            throw new ApplicationLogicException('Not all confirmations checked');
        }

        $action = WebRequest::postString('action');
        $availableRequestStates = $this->getSiteConfiguration()->getRequestStates();

        if ($action === EmailTemplate::CREATED || $action === EmailTemplate::NOT_CREATED) {

            if ($action === EmailTemplate::CREATED) {
                if ($this->checkAccountCreated($request)) {
                    $this->assignCSRFToken();
                    $this->showCustomCloseForm($database, $request);

                    $this->assign("preloadText", $messageBody);
                    $this->assign('preloadAction', $action);
                    $this->assign('ccMailingList', $ccMailingList);
                    $this->assign('showNonExistentAccountWarning', true);
                    $this->assign('skipAutoWelcome', WebRequest::postBoolean('skipAutoWelcome'));

                    return false;
                }
            }

            // Close request
            $this->closeRequest($request, $database, $action, $messageBody);

            $this->processWelcome($action, null);

            // Send the mail after the save, since save can be rolled back
            $this->sendMail($request, $messageBody, $currentUser, $ccMailingList);

            return true;
        }

        if ($action === self::CREATE_OAUTH || $action === self::CREATE_BOT) {
            $this->processAutoCreation($currentUser, $action, $request, $messageBody, $ccMailingList);

            return true;
        }

        // If action is a state key, defer to other state
        if (array_key_exists($action, $availableRequestStates)) {
            $this->deferRequest($request, $database, $action, $availableRequestStates, $messageBody);

            // Send the mail after the save, since save can be rolled back
            $this->sendMail($request, $messageBody, $currentUser, $ccMailingList);

            return true;
        }

        // Any other scenario, just send the email.

        $request->setReserved(null);
        $request->setUpdateVersion(WebRequest::postInt('updateversion'));
        $request->save();

        // Perform the notifications and stuff *after* we've successfully saved, since the save can throw an OLE
        // and be rolled back.

        // Send mail
        $this->sendMail($request, $messageBody, $currentUser, $ccMailingList);

        Logger::sentMail($database, $request, $messageBody);
        Logger::unreserve($database, $request);

        $this->getNotificationHelper()->sentMail($request);
        SessionAlert::success("Sent mail to Request {$request->getId()}");

        return true;
    }

    /**
     * @param Request     $request
     * @param PdoDatabase $database
     * @param string      $action
     * @param string      $messageBody
     *
     * @throws Exception
     * @throws OptimisticLockFailedException
     */
    protected function closeRequest(Request $request, PdoDatabase $database, $action, $messageBody)
    {
        $request->setStatus('Closed');
        $request->setReserved(null);
        $request->setUpdateVersion(WebRequest::postInt('updateversion'));
        $request->save();

        // Perform the notifications and stuff *after* we've successfully saved, since the save can throw an OLE and
        // be rolled back.

        if ($action == EmailTemplate::CREATED) {
            $logCloseType = 'custom-y';
            $notificationCloseType = "Custom, Created";
        }
        else {
            $logCloseType = 'custom-n';
            $notificationCloseType = "Custom, Not Created";
        }

        Logger::closeRequest($database, $request, $logCloseType, $messageBody);
        $this->getNotificationHelper()->requestClosed($request, $notificationCloseType);

        $requestName = htmlentities($request->getName(), ENT_COMPAT, 'UTF-8');
        SessionAlert::success("Request {$request->getId()} ({$requestName}) closed as {$notificationCloseType}.");
    }

    /**
     * @param Request     $request
     * @param PdoDatabase $database
     * @param string      $action
     * @param             $availableRequestStates
     * @param string      $messageBody
     *
     * @throws Exception
     * @throws OptimisticLockFailedException
     */
    protected function deferRequest(
        Request $request,
        PdoDatabase $database,
        $action,
        $availableRequestStates,
        $messageBody
    ) {
        $request->setStatus($action);
        $request->setReserved(null);
        $request->setUpdateVersion(WebRequest::postInt('updateversion'));
        $request->save();

        // Perform the notifications and stuff *after* we've successfully saved, since the save can throw an OLE
        // and be rolled back.

        $deferToLog = $availableRequestStates[$action]['defertolog'];
        Logger::sentMail($database, $request, $messageBody);
        Logger::deferRequest($database, $request, $deferToLog);

        $this->getNotificationHelper()->requestDeferredWithMail($request);

        $deferTo = $availableRequestStates[$action]['deferto'];
        SessionAlert::success("Request {$request->getId()} deferred to $deferTo, sending an email.");
    }

    /**
     * @param User    $currentUser
     * @param string  $action
     * @param Request $request
     * @param string  $messageBody
     * @param bool    $ccMailingList
     *
     * @throws AccessDeniedException
     * @throws ApplicationLogicException
     * @throws OptimisticLockFailedException
     */
    protected function processAutoCreation(User $currentUser, string $action, Request $request, string $messageBody, bool $ccMailingList): void
    {
        $db = $this->getDatabase();
        $oauth = new OAuthUserHelper($currentUser, $db, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());
        $canOauthCreate = $this->barrierTest(User::CREATION_OAUTH, $currentUser, 'RequestCreation');
        $canBotCreate = $this->barrierTest(User::CREATION_BOT, $currentUser, 'RequestCreation');
        $canOauthCreate &= $oauth->canCreateAccount();
        $canBotCreate &= $this->getSiteConfiguration()->getCreationBotPassword() !== null;

        $creationTaskClass = null;

        if ($action === self::CREATE_OAUTH) {
            if(!$canOauthCreate) {
                throw new AccessDeniedException($this->getSecurityManager());
            }

            $creationTaskClass = UserCreationTask::class;
        }

        if ($action === self::CREATE_BOT) {
            if (!$canBotCreate) {
                throw new AccessDeniedException($this->getSecurityManager());
            }

            $creationTaskClass = BotCreationTask::class;
        }

        if ($creationTaskClass === null) {
            throw new ApplicationLogicException('Cannot determine creation mode');
        }

        $request->setStatus(RequestStatus::JOBQUEUE);
        $request->setReserved(null);
        $request->save();

        $parameters = [
            'emailText' => $messageBody,
            'ccMailingList' => $ccMailingList
        ];

        $creationTask = new JobQueue();
        $creationTask->setTask($creationTaskClass);
        $creationTask->setRequest($request->getId());
        $creationTask->setTriggerUserId($currentUser->getId());
        $creationTask->setParameters(json_encode($parameters));
        $creationTask->setDatabase($db);
        $creationTask->save();

        $creationTaskId = $creationTask->getId();

        Logger::enqueuedJobQueue($db, $request);
        $this->getNotificationHelper()->requestCloseQueued($request, 'Custom, Created');

        SessionAlert::success("Request {$request->getId()} has been queued for autocreation");

        // forge this since it is actually a creation.
        $this->processWelcome(EmailTemplate::CREATED, $creationTaskId);
    }
}

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Exception;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Fragments\RequestData;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\SessionAlert;
use Waca\WebRequest;

class PageCustomClose extends PageCloseRequest
{
    use RequestData;

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
            $this->doCustomClose($currentUser, $request, $database);

            $this->redirect();
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
        $this->setupPrivateData($request, $currentUser, $this->getSiteConfiguration(), $database);

        // IP location
        $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp($request->getIp(), $request->getForwardedIp());
        $this->assign('iplocation', $this->getLocationProvider()->getIpLocation($trustedIp));

        // Confirmations
        $this->assign('confirmEmailAlreadySent', $this->checkEmailAlreadySent($request));
        $this->assign('confirmReserveOverride', $this->checkReserveOverride($request, $currentUser));

        $this->assign('canSkipCcMailingList', $this->barrierTest('skipCcMailingList', $currentUser));

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
    protected function doCustomClose(User $currentUser, Request $request, PdoDatabase $database)
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

        if (!(WebRequest::postBoolean('confirmEmailAlreadySent')
            && WebRequest::postBoolean('confirmReserveOverride'))
        ) {
            throw new ApplicationLogicException('Not all confirmations checked');
        }

        $action = WebRequest::postString('action');
        $availableRequestStates = $this->getSiteConfiguration()->getRequestStates();

        if ($action === EmailTemplate::CREATED || $action === EmailTemplate::NOT_CREATED) {
            // Close request
            $this->closeRequest($request, $database, $action, $messageBody);

            // Send the mail after the save, since save can be rolled back
            $this->sendMail($request, $messageBody, $currentUser, $ccMailingList);
        }
        else {
            if (array_key_exists($action, $availableRequestStates)) {
                // Defer to other state
                $this->deferRequest($request, $database, $action, $availableRequestStates, $messageBody);

                // Send the mail after the save, since save can be rolled back
                $this->sendMail($request, $messageBody, $currentUser, $ccMailingList);
            }
            else {
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
            }
        }
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
        SessionAlert::success("Request {$request->getId()} ({$requestName}) marked as 'Done'.");
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
}

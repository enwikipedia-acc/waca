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
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\RequestEmailHelper;
use Waca\PdoDatabase;
use Waca\SessionAlert;
use Waca\WebRequest;

class PageCloseRequest extends RequestActionBase
{
    protected function main()
    {
        $this->processClose();
    }

    /**
     * Main function for this page, when no specific actions are called.
     * @throws ApplicationLogicException
     */
    final protected function processClose()
    {
        $this->checkPosted();
        $database = $this->getDatabase();

        $currentUser = User::getCurrent($database);
        $template = $this->getTemplate($database);
        $request = $this->getRequest($database);
        $request->setUpdateVersion(WebRequest::postInt('updateversion'));

        if ($request->getStatus() === 'Closed') {
            throw new ApplicationLogicException('Request is already closed');
        }

        if ($this->confirmEmailAlreadySent($request, $template)) {
            return;
        }

        if ($this->checkReserveProtect($request, $currentUser)) {
            return;
        }

        if ($this->confirmAccountCreated($request, $template)) {
            return;
        }

        // I think we're good here...
        $request->setStatus('Closed');
        $request->setReserved(null);

        Logger::closeRequest($database, $request, $template->getId(), null);

        $request->save();

        $this->processWelcome($template->getDefaultAction(), null);

        // Perform the notifications and stuff *after* we've successfully saved, since the save can throw an OLE and
        // be rolled back.

        $this->getNotificationHelper()->requestClosed($request, $template->getName());
        $sanitisedTemplateName = htmlentities($template->getName(), ENT_COMPAT, 'UTF-8');
        SessionAlert::success("Request {$request->getId()} has been closed as {$sanitisedTemplateName}");

        $this->sendMail($request, $template->getText(), $currentUser, false);

        $this->redirect();
    }

    /**
     * @param PdoDatabase $database
     *
     * @return EmailTemplate
     * @throws ApplicationLogicException
     */
    protected function getTemplate(PdoDatabase $database)
    {
        $templateId = WebRequest::postInt('template');
        if ($templateId === null) {
            throw new ApplicationLogicException('No template specified');
        }

        /** @var EmailTemplate $template */
        $template = EmailTemplate::getById($templateId, $database);
        if ($template === false || !$template->getActive()) {
            throw new ApplicationLogicException('Invalid or inactive template specified');
        }

        return $template;
    }

    /**
     * @param Request       $request
     * @param EmailTemplate $template
     *
     * @return bool
     */
    protected function confirmEmailAlreadySent(Request $request, EmailTemplate $template)
    {
        if ($this->checkEmailAlreadySent($request)) {
            $this->showConfirmation($request, $template, 'close-confirmations/email-sent.tpl');

            return true;
        }

        return false;
    }

    protected function checkEmailAlreadySent(Request $request)
    {
        if ($request->getEmailSent() && !WebRequest::postBoolean('emailSentOverride')) {
            return true;
        }

        return false;
    }

    protected function checkReserveProtect(Request $request, User $currentUser)
    {
        $reservationId = $request->getReserved();

        if ($reservationId !== 0 && $reservationId !== null) {
            if ($currentUser->getId() !== $reservationId) {
                SessionAlert::error("Request is reserved by someone else.");
                $this->redirect('/viewRequest', null, ['id' => $request->getId()]);
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request       $request
     * @param EmailTemplate $template
     *
     * @return bool
     * @throws Exception
     */
    protected function confirmAccountCreated(Request $request, EmailTemplate $template)
    {
        if ($template->getDefaultAction() === EmailTemplate::CREATED && $this->checkAccountCreated($request)) {
            $this->showConfirmation($request, $template, 'close-confirmations/account-created.tpl');

            return true;
        }

        return false;
    }

    protected function checkAccountCreated(Request $request)
    {
        if (!WebRequest::postBoolean('createOverride')) {
            $parameters = array(
                'action'  => 'query',
                'list'    => 'users',
                'format'  => 'php',
                'ususers' => $request->getName(),
            );

            $content = $this->getHttpHelper()->get($this->getSiteConfiguration()->getMediawikiWebServiceEndpoint(),
                $parameters);

            $apiResult = unserialize($content);
            $exists = !isset($apiResult['query']['users']['0']['missing']);

            if (!$exists) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     * @param string  $mailText
     * @param User    $currentUser
     * @param boolean $ccMailingList
     */
    protected function sendMail(Request $request, $mailText, User $currentUser, $ccMailingList)
    {
        if (
        ($request->getEmail() != $this->getSiteConfiguration()->getDataClearEmail()) && 
        ($request->getIp() != $this->getSiteConfiguration()->getDataClearIp())
        ) {
            $requestEmailHelper = new RequestEmailHelper($this->getEmailHelper());
            $requestEmailHelper->sendMail($request, $mailText, $currentUser, $ccMailingList);
            
            $request->setEmailSent(true);
            $request->save();
        }
    }

    /**
     * @param Request       $request
     * @param EmailTemplate $template
     * @param string        $templateName
     *
     * @throws Exception
     * @return void
     */
    protected function showConfirmation(Request $request, EmailTemplate $template, $templateName)
    {
        $this->assignCSRFToken();

        $this->assign('request', $request->getId());
        $this->assign('template', $template->getId());

        $this->assign('updateversion', $request->getUpdateVersion());

        $this->assign('emailSentOverride', WebRequest::postBoolean('emailSentOverride') ? 'true' : 'false');
        $this->assign('reserveOverride', WebRequest::postBoolean('reserveOverride') ? 'true' : 'false');
        $this->assign('createOverride', WebRequest::postBoolean('createOverride') ? 'true' : 'false');

        $this->skipAlerts();

        $this->setTemplate($templateName);
    }

    /**
     * @param string $action
     * @param int|null   $parentTaskId
     *
     * @throws ApplicationLogicException
     */
    final protected function processWelcome(string $action, ?int $parentTaskId): void
    {
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        if ($action !== EmailTemplate::CREATED) {
            return;
        }

        if ($currentUser->getWelcomeTemplate() === null) {
            return;
        }

        $oauth = new OAuthUserHelper($currentUser, $database, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());
        if (!$oauth->canWelcome()) {
            return;
        }

        if (WebRequest::postBoolean('skipAutoWelcome')) {
            return;
        }

        $this->enqueueWelcomeTask($this->getRequest($database), $parentTaskId, $currentUser, $database);
    }
}

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Background;

use Exception;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Interfaces\IMediaWikiClient;
use Waca\Helpers\Logger;
use Waca\Helpers\MediaWikiHelper;
use Waca\Helpers\RequestEmailHelper;
use Waca\RequestStatus;

abstract class CreationTaskBase extends BackgroundTaskBase
{
    /** @var Request */
    private $request;
    /**
     * @var MediaWikiHelper
     * Don't use this directly.
     */
    private $mwHelper = null;

    public function execute()
    {
        $this->request = $this->getRequest();
        $user = $this->getTriggerUser();
        $parameters = $this->getParameters();

        if ($this->request->getStatus() !== RequestStatus::JOBQUEUE) {
            $this->markCancelled('Request is not deferred to the job queue');

            return;
        }

        if ($this->request->getEmailSent() != 0 && !isset($parameters->emailText)) {
            $this->markFailed('Request has already been sent a templated email');

            return;
        }

        if ($this->request->getEmail() === $this->getSiteConfiguration()->getDataClearEmail()) {
            $this->markFailed('Private data of request has been purged.');

            return;
        }

        $emailText = null;
        $ccMailingList = null;
        $logTarget = null;

        if (isset($parameters->emailText) && isset($parameters->ccMailingList)) {
            $emailText = $parameters->emailText;
            $ccMailingList = $parameters->ccMailingList;
            $logTarget = "custom-y";
        }

        if ($this->getEmailTemplate() !== null) {
            $emailText = $this->getEmailTemplate()->getText();
            $ccMailingList = false;
            $logTarget = $this->getEmailTemplate()->getId();
        }

        if ($emailText === null || $ccMailingList === null) {
            $this->markFailed('Unable to get closure email text');

            return;
        }

        try {
            $this->performCreation($user);

            $this->request->setStatus(RequestStatus::CLOSED);
            $this->request->setReserved(null);
            $this->request->setEmailSent(true);
            $this->request->save();

            // Log the closure as the user
            $logComment = $this->getEmailTemplate() === null ? $emailText : null;
            Logger::closeRequest($this->getDatabase(), $this->request, $logTarget, $logComment, $this->getTriggerUser());

            $requestEmailHelper = new RequestEmailHelper($this->getEmailHelper());
            $requestEmailHelper->sendMail($this->request, $emailText, $this->getTriggerUser(), $ccMailingList);
        }
        catch (Exception $ex) {
            $this->markFailed($ex->getMessage());

            return;
        }

        $this->markComplete();
    }

    /**
     * @return IMediaWikiClient
     */
    protected abstract function getMediaWikiClient();

    protected function getMediaWikiHelper()
    {
        if ($this->mwHelper === null) {
            $this->mwHelper = new MediaWikiHelper($this->getMediaWikiClient(), $this->getSiteConfiguration());
        }

        return $this->mwHelper;
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function getCreationReason(Request $request, User $user)
    {
        return 'Requested account at [[WP:ACC]], request #' . $request->getId();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function checkAccountExists($name)
    {
        return $this->getMediaWikiHelper()->checkAccountExists($name);
    }

    protected function markFailed($reason = null, bool $acknowledged = false)
    {
        $this->request->setStatus(RequestStatus::HOSPITAL);
        $this->request->save();

        $this->getNotificationHelper()->requestCreationFailed($this->request, $this->getTriggerUser());

        Logger::hospitalised($this->getDatabase(), $this->request);

        // auto-acknowledge failed creation tasks, as these land in the hospital queue anyway.
        parent::markFailed($reason, true);
        Logger::backgroundJobAcknowledged($this->getDatabase(), $this->getJob(), "Auto-acknowledged due to request deferral to hospital queue");
    }

    /**
     * @param $user
     *
     * @throws ApplicationLogicException
     */
    protected function performCreation($user)
    {
        $mw = $this->getMediaWikiHelper();

        $reason = $this->getCreationReason($this->request, $user);

        if ($this->checkAccountExists($this->request->getName())) {
            throw new ApplicationLogicException('Account already exists');
        }

        $mw->createAccount($this->request->getName(), $this->request->getEmail(), $reason);

        if (!$this->checkAccountExists($this->request->getName())) {
            throw new ApplicationLogicException('Account creation appeared to succeed but account does not exist.');
        }

        $this->request->setStatus(RequestStatus::CLOSED);
        $this->request->save();
    }
}
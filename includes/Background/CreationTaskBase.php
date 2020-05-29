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

        if ($this->request->getStatus() !== RequestStatus::JOBQUEUE) {
            $this->markCancelled('Request is not deferred to the job queue');

            return;
        }

        if ($this->request->getEmailSent() != 0) {
            $this->markFailed('Request has already been sent an email');

            return;
        }

        if ($this->getEmailTemplate() === null) {
            $this->markFailed('No email template specified');

            return;
        }

        try {
            $this->performCreation($user);

            $this->request->setStatus(RequestStatus::CLOSED);
            $this->request->setReserved(null);
            $this->request->save();

            // Log the closure as the user
            Logger::closeRequest($this->getDatabase(), $this->request, $this->getEmailTemplate()->getId(), null,
                $this->getTriggerUser());

            $requestEmailHelper = new RequestEmailHelper($this->getEmailHelper());
            $requestEmailHelper->sendMail($this->request, $this->getEmailTemplate()->getText(), $this->getTriggerUser(),
                false);

            $this->getNotificationHelper()->requestClosed($this->request, $this->getEmailTemplate()->getName());
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

    protected function getMediaWikiHelper(){
        if($this->mwHelper === null) {
            $this->mwHelper = new MediaWikiHelper($this->getMediaWikiClient(), $this->getSiteConfiguration());
        }

        return $this->mwHelper;
    }

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

    protected function markFailed($reason = null)
    {
        $this->request->setStatus(RequestStatus::HOSPITAL);
        $this->request->save();

        $this->getNotificationHelper()->requestCreationFailed($this->request);

        Logger::hospitalised($this->getDatabase(), $this->request);

        parent::markFailed($reason);
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
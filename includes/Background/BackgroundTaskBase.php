<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Background;

use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IEmailHelper;
use Waca\Helpers\Interfaces\IOAuthProtocolHelper;
use Waca\Helpers\IrcNotificationHelper;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

abstract class BackgroundTaskBase
{
    /** @var JobQueue */
    private $job;
    /** @var PdoDatabase */
    private $database;
    /** @var IOAuthProtocolHelper */
    private $oauthProtocolHelper;
    /** @var SiteConfiguration */
    private $siteConfiguration;
    /** @var IEmailHelper */
    private $emailHelper;
    /** @var HttpHelper */
    private $httpHelper;
    /** @var IrcNotificationHelper */
    private $notificationHelper;
    /** @var User */
    private $triggerUser;
    /** @var Request */
    private $request;
    /** @var EmailTemplate */
    private $emailTemplate = null;
    /** @var mixed */
    private $parameters;

    /**
     * @return JobQueue
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param JobQueue $job
     */
    public function setJob(JobQueue $job)
    {
        $this->job = $job;
    }

    /**
     * @return PdoDatabase
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param PdoDatabase $database
     */
    public function setDatabase(PdoDatabase $database)
    {
        $this->database = $database;
    }

    /**
     * @return IOAuthProtocolHelper
     */
    public function getOauthProtocolHelper()
    {
        return $this->oauthProtocolHelper;
    }

    /**
     * @param IOAuthProtocolHelper $oauthProtocolHelper
     */
    public function setOauthProtocolHelper(IOAuthProtocolHelper $oauthProtocolHelper)
    {
        $this->oauthProtocolHelper = $oauthProtocolHelper;
    }

    /**
     * @return SiteConfiguration
     */
    public function getSiteConfiguration()
    {
        return $this->siteConfiguration;
    }

    /**
     * @param SiteConfiguration $siteConfiguration
     */
    public function setSiteConfiguration(SiteConfiguration $siteConfiguration)
    {
        $this->siteConfiguration = $siteConfiguration;
    }

    /**
     * @return HttpHelper
     */
    public function getHttpHelper()
    {
        return $this->httpHelper;
    }

    /**
     * @param HttpHelper $httpHelper
     */
    public function setHttpHelper(HttpHelper $httpHelper)
    {
        $this->httpHelper = $httpHelper;
    }

    /**
     * @return IEmailHelper
     */
    public function getEmailHelper()
    {
        return $this->emailHelper;
    }

    /**
     * @param IEmailHelper $emailHelper
     */
    public function setEmailHelper(IEmailHelper $emailHelper)
    {
        $this->emailHelper = $emailHelper;
    }

    /**
     * @return IrcNotificationHelper
     */
    public function getNotificationHelper()
    {
        return $this->notificationHelper;
    }

    /**
     * @param IrcNotificationHelper $notificationHelper
     */
    public function setNotificationHelper($notificationHelper)
    {
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * @return void
     */
    protected abstract function execute();

    public function run()
    {
        $this->triggerUser = User::getById($this->job->getTriggerUserId(), $this->getDatabase());

        if ($this->triggerUser === false) {
            throw new ApplicationLogicException('Cannot locate trigger user');
        }

        $this->request = Request::getById($this->job->getRequest(), $this->getDatabase());

        if ($this->request === false) {
            throw new ApplicationLogicException('Cannot locate request');
        }

        if ($this->job->getEmailTemplate() !== null) {
            $this->emailTemplate = EmailTemplate::getById($this->job->getEmailTemplate(), $this->getDatabase());

            if ($this->emailTemplate === false) {
                throw new ApplicationLogicException('Cannot locate email template');
            }
        }

        if ($this->job->getParameters() !== null) {
            $this->parameters = json_decode($this->job->getParameters());

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApplicationLogicException('JSON decode: ' . json_last_error_msg());
            }
        }

        // Should we wait for a parent job?
        if ($this->job->getParent() !== null) {
            /** @var JobQueue $parentJob */
            $parentJob = JobQueue::getById($this->job->getParent(), $this->getDatabase());

            if ($parentJob === false) {
                $this->markFailed("Parent job could not be found");
                return;
            }

            switch ($parentJob->getStatus()) {
                case JobQueue::STATUS_CANCELLED:
                case JobQueue::STATUS_FAILED:
                    $this->markCancelled('Parent job failed/cancelled');
                    return;
                case JobQueue::STATUS_WAITING:
                case JobQueue::STATUS_READY:
                case JobQueue::STATUS_QUEUED:
                case JobQueue::STATUS_RUNNING:
                case JobQueue::STATUS_HELD:
                    // Defer to next execution
                    $this->job->setStatus(JobQueue::STATUS_READY);
                    $this->job->save();
                    return;
                case JobQueue::STATUS_COMPLETE:
                    // do nothing
                    break;
            }
        }

        $this->execute();
    }

    protected function markComplete()
    {
        $this->job->setStatus(JobQueue::STATUS_COMPLETE);
        $this->job->setError(null);
        $this->job->setAcknowledged(null);
        $this->job->save();

        Logger::backgroundJobComplete($this->getDatabase(), $this->getJob());
    }

    protected function markCancelled($reason = null)
    {
        $this->job->setStatus(JobQueue::STATUS_CANCELLED);
        $this->job->setError($reason);
        $this->job->setAcknowledged(null);
        $this->job->save();

        Logger::backgroundJobIssue($this->getDatabase(), $this->getJob());
    }

    protected function markFailed($reason = null, bool $acknowledged = false)
    {
        $this->job->setStatus(JobQueue::STATUS_FAILED);
        $this->job->setError($reason);
        $this->job->setAcknowledged($acknowledged ? 1 : 0);
        $this->job->save();

        Logger::backgroundJobIssue($this->getDatabase(), $this->getJob());
    }

    /**
     * @return User
     */
    public function getTriggerUser()
    {
        return $this->triggerUser;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return EmailTemplate
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}

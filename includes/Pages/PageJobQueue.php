<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\Background\Task\BotCreationTask;
use Waca\Background\Task\UserCreationTask;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\Helpers\LogHelper;
use Waca\Helpers\SearchHelpers\JobQueueSearchHelper;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\RequestStatus;
use Waca\Tasks\PagedInternalPageBase;
use Waca\WebRequest;

class PageJobQueue extends PagedInternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->setHtmlTitle('Job Queue Management');

        $this->prepareMaps();

        $database = $this->getDatabase();

        /** @var JobQueue[] $jobList */
        $jobList = JobQueueSearchHelper::get($database)
            ->statusIn(array('queued', 'ready', 'waiting', 'running', 'failed'))
            ->notAcknowledged()
            ->fetch();

        $userIds = array();
        $requestIds = array();

        foreach ($jobList as $job) {
            $userIds[] = $job->getTriggerUserId();
            $requestIds[] = $job->getRequest();

            $job->setDatabase($database);
        }

        $this->assign('canSeeAll', $this->barrierTest('all', User::getCurrent($database)));

        $this->assign('users', UserSearchHelper::get($database)->inIds($userIds)->fetchMap('username'));
        $this->assign('requests', RequestSearchHelper::get($database)->inIds($requestIds)->fetchMap('name'));

        $this->assign('joblist', $jobList);
        $this->setTemplate('jobqueue/main.tpl');
    }

    protected function all()
    {
        $this->setHtmlTitle('All Jobs');

        $this->prepareMaps();

        $database = $this->getDatabase();

        $searchHelper = JobQueueSearchHelper::get($database);
        $this->setSearchHelper($searchHelper);
        $this->setupLimits();

        $filterUser = WebRequest::getString('filterUser');
        $filterTask = WebRequest::getString('filterTask');
        $filterStatus = WebRequest::getString('filterStatus');
        $filterRequest = WebRequest::getString('filterRequest');
        $order = WebRequest::getString('order');

        if ($filterUser !== null) {
            $searchHelper->byUser(User::getByUsername($filterUser, $database)->getId());
        }

        if ($filterTask !== null) {
            $searchHelper->byTask($filterTask);
        }

        if ($filterStatus !== null) {
            $searchHelper->byStatus($filterStatus);
        }

        if ($filterRequest !== null) {
            $searchHelper->byRequest($filterRequest);
        }
        
        if ($order === null) {
            $searchHelper->newestFirst();
        }

        /** @var JobQueue[] $jobList */
        $jobList = $searchHelper->getRecordCount($count)->fetch();

        $this->setupPageData($count, array(
            'filterUser' => $filterUser,
            'filterTask' => $filterTask,
            'filterStatus' => $filterStatus,
            'filterRequest' => $filterRequest,
            'order' => $order,
        ));

        $userIds = array();
        $requestIds = array();

        foreach ($jobList as $job) {
            $userIds[] = $job->getTriggerUserId();
            $requestIds[] = $job->getRequest();

            $job->setDatabase($database);
        }

        $this->getTypeAheadHelper()->defineTypeAheadSource('username-typeahead', function() use ($database) {
            return UserSearchHelper::get($database)->fetchColumn('username');
        });

        $this->assign('users', UserSearchHelper::get($database)->inIds($userIds)->fetchMap('username'));
        $this->assign('requests', RequestSearchHelper::get($database)->inIds($requestIds)->fetchMap('name'));

        $this->assign('joblist', $jobList);

        $this->addJs("/api.php?action=users&all=true&targetVariable=typeaheaddata");

        $this->setTemplate('jobqueue/all.tpl');
    }

    protected function view()
    {
        $jobId = WebRequest::getInt('id');
        $database = $this->getDatabase();

        if ($jobId === null) {
            throw new ApplicationLogicException('No job specified');
        }

        /** @var JobQueue $job */
        $job = JobQueue::getById($jobId, $database);

        if ($job === false) {
            throw new ApplicationLogicException('Could not find requested job');
        }

        $this->prepareMaps();

        $this->assign('user', User::getById($job->getTriggerUserId(), $database));
        $this->assign('request', Request::getById($job->getRequest(), $database));
        $this->assign('emailTemplate', EmailTemplate::getById($job->getEmailTemplate(), $database));
        $this->assign('parent', JobQueue::getById($job->getParent(), $database));

        /** @var Log[] $logs */
        $logs = LogSearchHelper::get($database)->byObjectType('JobQueue')
            ->byObjectId($job->getId())->getRecordCount($logCount)->fetch();
        if ($logCount === 0) {
            $this->assign('log', array());
        }
        else {
            list($users, $logData) = LogHelper::prepareLogsForTemplate($logs, $database, $this->getSiteConfiguration());

            $this->assign("log", $logData);
            $this->assign("users", $users);
        }

        $this->assignCSRFToken();

        $this->assign('job', $job);

        $this->assign('canAcknowledge', $this->barrierTest('acknowledge', User::getCurrent($database)));
        $this->assign('canRequeue', $this->barrierTest('requeue', User::getCurrent($database)));
        $this->assign('canCancel', $this->barrierTest('cancel', User::getCurrent($database)));

        if ($job->getTask() === UserCreationTask::class || $job->getTask() === BotCreationTask::class) {
            if ($job->getEmailTemplate() === null) {
                $params = json_decode($job->getParameters());

                if (isset($params->emailText)) {
                    $this->assign("creationEmailText", $params->emailText);
                }
            }
        }

        $this->setHtmlTitle('Job #{$job->getId()|escape}');
        $this->setTemplate('jobqueue/view.tpl');
    }

    protected function acknowledge()
    {
        if (!WebRequest::wasPosted()) {
            throw new ApplicationLogicException('This page does not support GET methods.');
        }

        $this->validateCSRFToken();

        $jobId = WebRequest::postInt('job');
        $database = $this->getDatabase();

        if ($jobId === null) {
            throw new ApplicationLogicException('No job specified');
        }

        /** @var JobQueue $job */
        $job = JobQueue::getById($jobId, $database);

        if ($job === false) {
            throw new ApplicationLogicException('Could not find requested job');
        }

        $job->setUpdateVersion(WebRequest::postInt('updateVersion'));
        $job->setAcknowledged(true);
        $job->save();

        Logger::backgroundJobAcknowledged($database, $job);

        $this->redirect('jobQueue', 'view', array('id' => $jobId));
    }

    protected function cancel()
    {
        if (!WebRequest::wasPosted()) {
            throw new ApplicationLogicException('This page does not support GET methods.');
        }

        $this->validateCSRFToken();

        $jobId = WebRequest::postInt('job');
        $database = $this->getDatabase();

        if ($jobId === null) {
            throw new ApplicationLogicException('No job specified');
        }

        /** @var JobQueue $job */
        $job = JobQueue::getById($jobId, $database);

        if ($job === false) {
            throw new ApplicationLogicException('Could not find requested job');
        }

        if ($job->getStatus() !== JobQueue::STATUS_READY
            && $job->getStatus() !== JobQueue::STATUS_QUEUED
            && $job->getStatus() === JobQueue::STATUS_WAITING) {
            throw new ApplicationLogicException('Cannot cancel job not queued for execution');
        }

        $job->setUpdateVersion(WebRequest::postInt('updateVersion'));
        $job->setStatus(JobQueue::STATUS_CANCELLED);
        $job->setError("Manually cancelled");
        $job->setAcknowledged(null);
        $job->save();

        Logger::backgroundJobCancelled($this->getDatabase(), $job);

        $this->redirect('jobQueue', 'view', array('id' => $jobId));
    }

    protected function requeue()
    {
        if (!WebRequest::wasPosted()) {
            throw new ApplicationLogicException('This page does not support GET methods.');
        }

        $this->validateCSRFToken();

        $jobId = WebRequest::postInt('job');
        $database = $this->getDatabase();

        if ($jobId === null) {
            throw new ApplicationLogicException('No job specified');
        }

        /** @var JobQueue|false $job */
        $job = JobQueue::getById($jobId, $database);

        if ($job === false) {
            throw new ApplicationLogicException('Could not find requested job');
        }

        $job->setStatus(JobQueue::STATUS_QUEUED);
        $job->setUpdateVersion(WebRequest::postInt('updateVersion'));
        $job->setAcknowledged(null);
        $job->setError(null);
        $job->save();

        /** @var Request $request */
        $request = Request::getById($job->getRequest(), $database);
        $request->setStatus(RequestStatus::JOBQUEUE);
        $request->save();

        Logger::enqueuedJobQueue($database, $request);
        Logger::backgroundJobRequeued($database, $job);

        $this->redirect('jobQueue', 'view', array('id' => $jobId));
    }

    protected function prepareMaps()
    {
        $taskNameMap = JobQueue::getTaskDescriptions();

        $statusDecriptionMap = array(
            JobQueue::STATUS_CANCELLED => 'The job was cancelled',
            JobQueue::STATUS_COMPLETE  => 'The job completed successfully',
            JobQueue::STATUS_FAILED    => 'The job encountered an error',
            JobQueue::STATUS_QUEUED    => 'The job is in the queue',
            JobQueue::STATUS_READY     => 'The job is ready to be picked up by the next job runner execution',
            JobQueue::STATUS_RUNNING   => 'The job is being run right now by the job runner',
            JobQueue::STATUS_WAITING   => 'The job has been picked up by a job runner',
            JobQueue::STATUS_HELD      => 'The job has manually held from processing',
        );
        $this->assign('taskNameMap', $taskNameMap);
        $this->assign('statusDescriptionMap', $statusDecriptionMap);
    }
}

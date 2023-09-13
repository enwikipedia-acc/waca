<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use DateTime;
use Waca\DataObjects\Domain;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\Helpers\SearchHelpers\JobQueueSearchHelper;
use Waca\RequestStatus;
use Waca\SessionAlert;
use Waca\WebRequest;

class PageDeferRequest extends RequestActionBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @throws ApplicationLogicException
     */
    protected function main()
    {
        $this->checkPosted();
        $database = $this->getDatabase();
        $request = $this->getRequest($database);
        $currentUser = User::getCurrent($database);

        $target = WebRequest::postString('target');

        $domain = Domain::getCurrent($this->getDatabase());

        $requestQueue = RequestQueue::getByApiName($database, $target, $domain->getId());

        if ($requestQueue === false) {
            throw new ApplicationLogicException('Defer target not valid');
        }

        if ($request->getQueue() == $requestQueue->getId() && $request->getStatus() == RequestStatus::OPEN) {
            SessionAlert::warning('This request is already in the specified queue.');
            $this->redirect('viewRequest', null, array('id' => $request->getId()));

            return;
        }

        $closureDate = $request->getClosureDate();
        $date = new DateTime();
        $date->modify("-7 days");

        if ($request->getStatus() == RequestStatus::CLOSED && $closureDate < $date) {
            if (!$this->barrierTest('reopenOldRequest', $currentUser, 'RequestData')) {
                throw new ApplicationLogicException(
                    "You are not allowed to re-open a request that has been closed for over a week.");
            }
        }

        if ($request->getEmail() === $this->getSiteConfiguration()->getDataClearEmail()) {
            if (!$this->barrierTest('reopenClearedRequest', $currentUser, 'RequestData')) {
                throw new ApplicationLogicException(
                    "You are not allowed to re-open a request for which the private data has been purged.");
            }
        }

        if ($request->getStatus() === RequestStatus::JOBQUEUE) {
            /** @var JobQueue[] $pendingJobs */
            $pendingJobs = JobQueueSearchHelper::get($database, $request->getDomain())
                ->byRequest($request->getId())
                ->statusIn([
                    JobQueue::STATUS_QUEUED,
                    JobQueue::STATUS_READY,
                    JobQueue::STATUS_WAITING,
                ])
                ->fetch();

            foreach ($pendingJobs as $job) {
                $job->setStatus(JobQueue::STATUS_CANCELLED);
                $job->setError('Cancelled by request deferral');
                $job->save();

                Logger::backgroundJobCancelled($database, $job);
            }
        }

        $request->setReserved(null);
        $request->setStatus(RequestStatus::OPEN);
        $request->setQueue($requestQueue->getId());
        $request->setUpdateVersion(WebRequest::postInt('updateversion'));
        $request->save();

        Logger::deferRequest($database, $request, $requestQueue->getLogName());

        $this->getNotificationHelper()->requestDeferred($request);

        $deto = htmlentities($requestQueue->getDisplayName(), ENT_COMPAT, 'UTF-8');
        SessionAlert::success("Request {$request->getId()} deferred to {$deto}");

        $this->redirect();
    }
}

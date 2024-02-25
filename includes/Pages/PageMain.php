<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use PDO;
use Waca\DataObjects\Request;
use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\Helpers\PreferenceManager;
use Waca\Fragments\RequestListData;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\PdoDatabase;
use Waca\RequestStatus;
use Waca\SiteConfiguration;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageMain extends InternalPageBase
{
    use RequestListData;

    /**
     * Main function for this page, when no actions are called.
     */
    protected function main()
    {
        $this->assignCSRFToken();

        $config = $this->getSiteConfiguration();
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);
        $preferencesManager = PreferenceManager::getForCurrent($database);

        // general template configuration
        // FIXME: domains!
        $defaultQueue = RequestQueue::getDefaultQueue($database, 1);
        $this->assign('defaultRequestState', $defaultQueue->getApiName());
        $this->assign('requestLimitShowOnly', $config->getMiserModeLimit());

        $seeAllRequests = $this->barrierTest('seeAllRequests', $currentUser, PageViewRequest::class);

        list($defaultSort, $defaultSortDirection) = WebRequest::requestListDefaultSort();
        $this->assign('defaultSort', $defaultSort);
        $this->assign('defaultSortDirection', $defaultSortDirection);
        $showQueueHelp = $preferencesManager->getPreference(PreferenceManager::PREF_QUEUE_HELP) ?? true;
        $this->assign('showQueueHelp', $showQueueHelp);

        // Fetch request data
        $requestSectionData = array();
        if ($seeAllRequests) {
            $this->setupStatusSections($database, $config, $requestSectionData);
            $this->setupHospitalQueue($database, $config, $requestSectionData);
            $this->setupJobQueue($database, $config, $requestSectionData);
        }
        $this->setupLastFiveClosedData($database, $seeAllRequests);

        // Assign data to template
        $this->assign('requestSectionData', $requestSectionData);

        $this->setTemplate('mainpage/mainpage.tpl');
    }

    /**
     * @param PdoDatabase $database
     * @param bool        $seeAllRequests
     *
     * @internal param User $currentUser
     */
    private function setupLastFiveClosedData(PdoDatabase $database, $seeAllRequests)
    {
        $config = $this->getSiteConfiguration();
        $this->assign('showLastFive', $seeAllRequests);
        if (!$seeAllRequests) {
            return;
        }

        $queryExcludeDropped = "";
        if ($config->getEmailConfirmationEnabled()) {
            $queryExcludeDropped = "AND request.emailConfirm = 'Confirmed'";
        }

        $query = <<<SQL
		SELECT request.id, request.name, request.updateversion
		FROM request /* PageMain::main() */
		JOIN log ON log.objectid = request.id AND log.objecttype = 'Request'
		WHERE log.action LIKE 'Closed%'
		$queryExcludeDropped
		ORDER BY log.timestamp DESC
		LIMIT 5;
SQL;

        $statement = $database->prepare($query);
        $statement->execute();

        $last5result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->assign('lastFive', $last5result);
    }

    /**
     * @param PdoDatabase       $database
     * @param SiteConfiguration $config
     * @param                   $requestSectionData
     */
    private function setupHospitalQueue(
        PdoDatabase $database,
        SiteConfiguration $config,
        &$requestSectionData
    ) {
        // FIXME: domains!
        $search = RequestSearchHelper::get($database, 1)
            ->limit($config->getMiserModeLimit())
            ->excludingStatus('Closed')
            ->isHospitalised();

        if ($config->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }

        /** @var Request[] $results */
        $results = $search->getRecordCount($requestCount)->fetch();

        if ($requestCount > 0) {
            $requestSectionData['Hospital - Requests failed auto-creation'] = array(
                'requests' => $this->prepareRequestData($results),
                'total'    => $requestCount,
                'api'      => 'hospital',
                'type'     => 'hospital',
                'special'  => 'Job Queue',
                'help'     => 'This queue lists all the requests which have been attempted to be created in the background, but for which this has failed for one reason or another. Check the job queue to find the error. Requests here may need to be created manually, or it may be possible to re-queue the request for auto-creation by the tool, or it may have been created already. Use your own technical discretion here.',
                'showAll'  => false
            );
        }
    }

    /**
     * @param PdoDatabase       $database
     * @param SiteConfiguration $config
     * @param                   $requestSectionData
     */
    private function setupJobQueue(
        PdoDatabase $database,
        SiteConfiguration $config,
        &$requestSectionData
    ) {
        // FIXME: domains!
        $search = RequestSearchHelper::get($database, 1)
            ->limit($config->getMiserModeLimit())
            ->byStatus(RequestStatus::JOBQUEUE);

        if ($config->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }

        /** @var Request[] $results */
        $results = $search->getRecordCount($requestCount)->fetch();

        if ($requestCount > 0) {
            $requestSectionData['Requests queued in the Job Queue'] = array(
                'requests' => $this->prepareRequestData($results),
                'total'    => $requestCount,
                'api'      => 'JobQueue',
                'type'     => 'JobQueue',
                'special'  => 'Job Queue',
                'help'     => 'This section lists all the requests which are currently waiting to be created by the tool. Requests should automatically disappear from here within a few minutes.',
                'showAll'  => false
            );
        }
    }

    /**
     * @param PdoDatabase       $database
     * @param SiteConfiguration $config
     * @param                   $requestSectionData
     */
    private function setupStatusSections(
        PdoDatabase $database,
        SiteConfiguration $config,
        &$requestSectionData
    ) {
        // FIXME: domains!
        $search = RequestSearchHelper::get($database, 1)->limit($config->getMiserModeLimit());
        $search->byStatus(RequestStatus::OPEN);

        if ($config->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }

        // FIXME: domains!
        $requestQueues = RequestQueue::getAllQueues($database);
        $queuesById = array_reduce($requestQueues, function($result, RequestQueue $item) {
            $result[$item->getId()] = $item;
            return $result;
        }, array());

        $requestsByQueue = $search->fetchByQueue(array_keys($queuesById));

        foreach ($requestsByQueue as $queueId => $queueData) {
            if ($queueData['count'] > 0 || $queuesById[$queueId]->isEnabled()) {
                $requestSectionData[$queuesById[$queueId]->getHeader()] = array(
                    'requests' => $this->prepareRequestData($queueData['data']),
                    'total'    => $queueData['count'],
                    'api'      => $queuesById[$queueId]->getApiName(),
                    'type'     => $queueId,
                    'special'  => null,
                    'help'     => $queuesById[$queueId]->getHelp(),
                    'showAll'  => true
                );
            }
        }
    }
}

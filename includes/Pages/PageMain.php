<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use PDO;
use Waca\DataObjects\User;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Pages\RequestAction\PageBreakReservation;
use Waca\PdoDatabase;
use Waca\RequestStatus;
use Waca\SiteConfiguration;
use Waca\Tasks\InternalPageBase;

class PageMain extends InternalPageBase
{
    /**
     * Main function for this page, when no actions are called.
     */
    protected function main()
    {
        $this->assignCSRFToken();

        $config = $this->getSiteConfiguration();
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        // general template configuration
        $this->assign('defaultRequestState', $config->getDefaultRequestStateKey());
        $this->assign('requestLimitShowOnly', $config->getMiserModeLimit());

        // Get map of possible usernames
        $userList = UserSearchHelper::get($database)->withReservedRequest();
        $this->assign('userList', $userList);

        $seeAllRequests = $this->barrierTest('seeAllRequests', $currentUser, PageViewRequest::class);
        $this->assign('showPrivateData', $this->barrierTest('alwaysSeePrivateData', $currentUser, 'RequestData'));
        $this->assign('xff', $this->getXffTrustProvider());

        $this->assign('dataClearEmail', $config->getDataClearEmail());
        $this->assign('dataClearIp', $config->getDataClearIp());

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

        // Extra rights
        $this->assign('canBan', $this->barrierTest('set', $currentUser, PageBan::class));
        $this->assign('canBreakReservation', $this->barrierTest('force', $currentUser, PageBreakReservation::class));

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
        $this->assign('showLastFive', $seeAllRequests);
        if (!$seeAllRequests) {
            return;
        }

        $query = <<<SQL
		SELECT request.id, request.name, request.updateversion
		FROM request /* PageMain::main() */
		JOIN log ON log.objectid = request.id AND log.objecttype = 'Request'
		WHERE log.action LIKE 'Closed%'
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
        $search = RequestSearchHelper::get($database)
            ->limit($config->getMiserModeLimit())
            ->excludingStatus('Closed')
            ->isHospitalised();

        if ($config->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }

        $results = $search->getRecordCount($requestCount)->fetch();

        if($requestCount > 0) {
            $requestSectionData['Hospital - Requests failed auto-creation'] = array(
                'requests' => $results,
                'total'    => $requestCount,
                'api'      => 'hospital',
                'type'     => 'hospital',
                'special'  => 'Job Queue',
                'help'     => 'This queue lists all the requests which have been attempted to be created in the background, but for which this has failed for one reason or another. Check the job queue to find the error. Requests here may need to be created manually, or it may be possible to re-queue the request for auto-creation by the tool, or it may have been created already. Use your own technical discretion here.'
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
        $search = RequestSearchHelper::get($database)
            ->limit($config->getMiserModeLimit())
            ->byStatus(RequestStatus::JOBQUEUE);

        if ($config->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }

        $results = $search->getRecordCount($requestCount)->fetch();

        if($requestCount > 0) {
            $requestSectionData['Requests queued in the Job Queue'] = array(
                'requests' => $results,
                'total'    => $requestCount,
                'api'      => 'JobQueue',
                'type'     => 'JobQueue',
                'special'  => 'Job Queue',
                'help'     => 'This section lists all the requests which are currently waiting to be created by the tool. Requests should automatically disappear from here within a few minutes.'
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
        $search = RequestSearchHelper::get($database)->limit($config->getMiserModeLimit())->notHospitalised();

        if ($config->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }

        $allRequestStates = $config->getRequestStates();
        $requestsByStatus = $search->fetchByStatus(array_keys($allRequestStates));

        foreach ($allRequestStates as $requestState => $requestStateConfig) {

            $requestTrustedIp = [];
            $relatedEmailRequests = [];
            $relatedIpRequests = [];
            foreach ($requestsByStatus[$requestState]['data'] as $request) {
                $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp(
                    $request->getIp(),
                    $request->getForwardedIp()
                );

                RequestSearchHelper::get($database)
                    ->byIp($trustedIp)
                    ->withConfirmedEmail()
                    ->excludingPurgedData($config)
                    ->excludingRequest($request->getId())
                    ->getRecordCount($ipCount);

                RequestSearchHelper::get($database)
                    ->byEmailAddress($request->getEmail())
                    ->withConfirmedEmail()
                    ->excludingPurgedData($config)
                    ->excludingRequest($request->getId())
                    ->getRecordCount($emailCount);

                $requestTrustedIp[$request->getId()] = $trustedIp;
                $relatedEmailRequests[$request->getId()] = $emailCount;
                $relatedIpRequests[$request->getId()] = $ipCount;
            }


            $requestSectionData[$requestStateConfig['header']] = array(
                'requests' => $requestsByStatus[$requestState]['data'],
                'total'    => $requestsByStatus[$requestState]['count'],
                'api'      => $requestStateConfig['api'],
                'type'     => $requestState,
                'special'  => null,
                'help'     => $requestStateConfig['queuehelp'],
                'requestTrustedIp' => $requestTrustedIp,
                'relatedEmailRequests' => $relatedEmailRequests,
                'relatedIpRequests' => $relatedIpRequests
            );
        }
    }
}

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
use Waca\DataObjects\User;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Pages\RequestAction\PageBreakReservation;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageExpandedRequestList extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     * @todo This is very similar to the PageMain code, we could probably generalise this somehow
     */
    protected function main()
    {
        $config = $this->getSiteConfiguration();

        $requestedStatus = WebRequest::getString('status');
        $requestStates = $config->getRequestStates();

        if ($requestedStatus !== null && isset($requestStates[$requestedStatus])) {

            $this->assignCSRFToken();

            $database = $this->getDatabase();

            $help = $requestStates[$requestedStatus]['queuehelp'];
            $this->assign('queuehelp', $help);

            if ($config->getEmailConfirmationEnabled()) {
                $query = "SELECT * FROM request WHERE status = :type AND emailconfirm = 'Confirmed';";
                $totalQuery = "SELECT COUNT(id) FROM request WHERE status = :type AND emailconfirm = 'Confirmed';";
            }
            else {
                $query = "SELECT * FROM request WHERE status = :type;";
                $totalQuery = "SELECT COUNT(id) FROM request WHERE status = :type;";
            }

            $statement = $database->prepare($query);

            $totalRequestsStatement = $database->prepare($totalQuery);

            $this->assign('defaultRequestState', $config->getDefaultRequestStateKey());

            $type = $requestedStatus;

            $statement->bindValue(":type", $type);
            $statement->execute();

            $requests = $statement->fetchAll(PDO::FETCH_CLASS, Request::class);

            /** @var Request $req */
            foreach ($requests as $req) {
                $req->setDatabase($database);
            }

            $this->assign('requests', $requests);
            $this->assign('header', $type);

            $totalRequestsStatement->bindValue(':type', $type);
            $totalRequestsStatement->execute();
            $totalRequests = $totalRequestsStatement->fetchColumn();
            $totalRequestsStatement->closeCursor();
            $this->assign('totalRequests', $totalRequests);


            $this->setHtmlTitle('{$header|escape}{if $totalRequests > 0} [{$totalRequests|escape}]{/if}');

            $userIds = array_map(
                function(Request $entry) {
                    return $entry->getReserved();
                },
                $requests
            );

            $userList = UserSearchHelper::get($this->getDatabase())->inIds($userIds)->fetchMap('username');
            $this->assign('userList', $userList);

            $requestTrustedIp = [];
            $relatedEmailRequests = [];
            $relatedIpRequests = [];
            foreach ($requests as $request) {
                $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp(
                    $request->getIp(),
                    $request->getForwardedIp()
                );

                RequestSearchHelper::get($database)
                    ->byIp($trustedIp)
                    ->withConfirmedEmail()
                    ->excludingPurgedData($this->getSiteConfiguration())
                    ->excludingRequest($request->getId())
                    ->getRecordCount($ipCount);

                RequestSearchHelper::get($database)
                    ->byEmailAddress($request->getEmail())
                    ->withConfirmedEmail()
                    ->excludingPurgedData($this->getSiteConfiguration())
                    ->excludingRequest($request->getId())
                    ->getRecordCount($emailCount);

                $requestTrustedIp[$request->getId()] = $trustedIp;
                $relatedEmailRequests[$request->getId()] = $emailCount;
                $relatedIpRequests[$request->getId()] = $ipCount;
            }
            $this->assign('requestTrustedIp', $requestTrustedIp);
            $this->assign('relatedEmailRequests', $relatedEmailRequests);
            $this->assign('relatedIpRequests', $relatedIpRequests);

            $this->assign('requestLimitShowOnly', $config->getMiserModeLimit());

            $currentUser = User::getCurrent($database);
            $this->assign('canBan', $this->barrierTest('set', $currentUser, PageBan::class));
            $this->assign('canBreakReservation', $this->barrierTest('force', $currentUser, PageBreakReservation::class));

            $this->assign('showPrivateData', $this->barrierTest('alwaysSeePrivateData', $currentUser, 'RequestData'));
            $this->assign('xff', $this->getXffTrustProvider());

            $this->assign('dataClearEmail', $this->getSiteConfiguration()->getDataClearEmail());
            $this->assign('dataClearIp', $this->getSiteConfiguration()->getDataClearIp());

            $this->setTemplate('mainpage/expandedrequestlist.tpl');
        }
    }
}

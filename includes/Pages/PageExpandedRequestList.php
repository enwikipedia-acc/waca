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
use Waca\Fragments\RequestListData;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageExpandedRequestList extends InternalPageBase
{
    use RequestListData;

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

            $statement->bindValue(":type", $requestedStatus);
            $statement->execute();

            $requests = $statement->fetchAll(PDO::FETCH_CLASS, Request::class);

            /** @var Request $req */
            foreach ($requests as $req) {
                $req->setDatabase($database);
            }

            $this->assign('requests', $this->prepareRequestData($requests));
            $this->assign('header', $requestedStatus);
            $this->assign('requestLimitShowOnly', $config->getMiserModeLimit());
            $this->assign('defaultRequestState', $config->getDefaultRequestStateKey());

            $totalRequestsStatement->bindValue(':type', $requestedStatus);
            $totalRequestsStatement->execute();
            $totalRequests = $totalRequestsStatement->fetchColumn();
            $totalRequestsStatement->closeCursor();
            $this->assign('totalRequests', $totalRequests);


            $this->setHtmlTitle('{$header|escape}{if $totalRequests > 0} [{$totalRequests|escape}]{/if}');
            $this->setTemplate('mainpage/expandedrequestlist.tpl');
        }
    }
}

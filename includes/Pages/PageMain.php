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
use Waca\Security\SecurityConfiguration;
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

        $requestSectionData = array();

        if ($config->getEmailConfirmationEnabled()) {
            $query = "SELECT * FROM request WHERE status = :type AND emailconfirm = 'Confirmed' LIMIT :lim;";
            $totalQuery = "SELECT COUNT(id) FROM request WHERE status = :type AND emailconfirm = 'Confirmed';";
        }
        else {
            $query = "SELECT * FROM request WHERE status = :type LIMIT :lim;";
            $totalQuery = "SELECT COUNT(id) FROM request WHERE status = :type;";
        }

        $statement = $database->prepare($query);
        $statement->bindValue(':lim', $config->getMiserModeLimit(), PDO::PARAM_INT);

        $totalRequestsStatement = $database->prepare($totalQuery);

        $this->assign('defaultRequestState', $config->getDefaultRequestStateKey());

        foreach ($config->getRequestStates() as $type => $v) {
            $statement->bindValue(":type", $type);
            $statement->execute();

            $requests = $statement->fetchAll(PDO::FETCH_CLASS, Request::class);

            /** @var Request $req */
            foreach ($requests as $req) {
                $req->setDatabase($database);
            }

            $totalRequestsStatement->bindValue(':type', $type);
            $totalRequestsStatement->execute();
            $totalRequests = $totalRequestsStatement->fetchColumn();
            $totalRequestsStatement->closeCursor();

            $userIds = array_map(
                function(Request $entry) {
                    return $entry->getReserved();
                },
                $requests);
            $userList = User::getUsernames($userIds, $this->getDatabase());
            $this->assign('userlist', $userList);

            $requestSectionData[$v['header']] = array(
                'requests' => $requests,
                'total'    => $totalRequests,
                'api'      => $v['api'],
                'type'     => $type,
                'userlist' => $userList,
            );
        }

        $this->assign('requestLimitShowOnly', $config->getMiserModeLimit());

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
        $this->assign('requestSectionData', $requestSectionData);

        $this->setTemplate('mainpage/mainpage.tpl');
    }

    /**
     * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
     * the return value from this function.
     *
     * If this page even supports actions, you will need to check the route
     *
     * @return SecurityConfiguration
     * @category Security-Critical
     */
    protected function getSecurityConfiguration()
    {
        return $this->getSecurityManager()->configure()->asInternalPage();
    }
}
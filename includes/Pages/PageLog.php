<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Log;
use Waca\DataObjects\User;
use Waca\Helpers\LogHelper;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\Security\SecurityConfiguration;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageLog extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main()
    {
        $this->setHtmlTitle('Logs');

        $filterUser = WebRequest::getString('filterUser');
        $filterAction = WebRequest::getString('filterAction');

        $database = $this->getDatabase();

        $this->getTypeAheadHelper()->defineTypeAheadSource('username-typeahead', function() use ($database) {
            return User::getAllUsernames($database);
        });

        $limit = WebRequest::getInt('limit');
        if ($limit === null) {
            $limit = 100;
        }

        $page = WebRequest::getInt('page');
        if ($page === null) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $logSearch = LogSearchHelper::get($database)->limit($limit, $offset);
        if ($filterUser !== null) {
            $logSearch->byUser(User::getByUsername($filterUser, $database)->getId());
        }

        if ($filterAction !== null) {
            $logSearch->byAction($filterAction);
        }

        /** @var Log[] $logs */
        $logs = $logSearch->getRecordCount($count)->fetch();

        if ($count === 0) {
            $this->assign('logs', array());
            $this->setTemplate('logs/main.tpl');

            return;
        }

        list($users, $logData) = LogHelper::prepareLogsForTemplate($logs, $database, $this->getSiteConfiguration());

        $this->setupPageData($page, $limit, $count);

        $this->assign("logs", $logData);
        $this->assign("users", $users);

        $this->assign("filterUser", $filterUser);
        $this->assign("filterAction", $filterAction);

        $this->assign('allLogActions', LogHelper::getLogActions($this->getDatabase()));

        $this->setTemplate("logs/main.tpl");
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

    /**
     * @param int $page
     * @param int $limit
     * @param int $count
     */
    protected function setupPageData($page, $limit, $count)
    {
        // The number of pages on the pager to show. Must be odd
        $pageLimit = 9;

        $pageData = array(
            // Can the user go to the previous page?
            'canprev'   => $page != 1,
            // Can the user go to the next page?
            'cannext'   => ($page * $limit) < $count,
            // Maximum page number
            'maxpage'   => ceil($count / $limit),
            // Limit to the number of pages to display
            'pagelimit' => $pageLimit,
        );

        // number of pages either side of the current to show
        $pageMargin = (($pageLimit - 1) / 2);

        // Calculate the number of pages either side to show - this is for situations like:
        //  [1]  [2] [[3]] [4]  [5]  [6]  [7]  [8]  [9] - where you can't just use the page margin calculated
        $pageData['lowpage'] = max(1, $page - $pageMargin);
        $pageData['hipage'] = min($pageData['maxpage'], $page + $pageMargin);
        $pageCount = ($pageData['hipage'] - $pageData['lowpage']) + 1;

        if ($pageCount < $pageLimit) {
            if ($pageData['lowpage'] == 1 && $pageData['hipage'] < $pageData['maxpage']) {
                $pageData['hipage'] = min($pageLimit, $pageData['maxpage']);
            }
            elseif ($pageData['lowpage'] > 1 && $pageData['hipage'] == $pageData['maxpage']) {
                $pageData['lowpage'] = max(1, $pageData['maxpage'] - $pageLimit + 1);
            }
        }

        // Put the range of pages into the page data
        $pageData['pages'] = range($pageData['lowpage'], $pageData['hipage']);

        $this->assign("pagedata", $pageData);

        $this->assign("limit", $limit);
        $this->assign("page", $page);
    }
}
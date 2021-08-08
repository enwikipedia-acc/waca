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
use Waca\Tasks\PagedInternalPageBase;
use Waca\WebRequest;

class PageLog extends PagedInternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main()
    {
        $this->setHtmlTitle('Logs');

        $filterUser = WebRequest::getString('filterUser');
        $filterAction = WebRequest::getString('filterAction');
        $filterObjectType = WebRequest::getString('filterObjectType');
        $filterObjectId = WebRequest::getInt('filterObjectId');

        $database = $this->getDatabase();

        if (!array_key_exists($filterObjectType, LogHelper::getObjectTypes())) {
            $filterObjectType = null;
        }

        $this->addJs("/api.php?action=users&all=true&targetVariable=typeaheaddata");

        $logSearch = LogSearchHelper::get($database);

        if ($filterUser !== null) {
            $userObj = User::getByUsername($filterUser, $database);
            if ($userObj !== false) {
                $logSearch->byUser($userObj->getId());
            }
            else {
                $logSearch->byUser(-1);
            }
        }
        if ($filterAction !== null) {
            $logSearch->byAction($filterAction);
        }
        if ($filterObjectType !== null) {
            $logSearch->byObjectType($filterObjectType);
        }
        if ($filterObjectId !== null) {
            $logSearch->byObjectId($filterObjectId);
        }

        $this->setSearchHelper($logSearch);
        $this->setupLimits();

        /** @var Log[] $logs */
        $logs = $logSearch->getRecordCount($count)->fetch();

        list($users, $logData) = LogHelper::prepareLogsForTemplate($logs, $database, $this->getSiteConfiguration());

        $this->setupPageData($count, array('filterUser' => $filterUser, 'filterAction' => $filterAction, 'filterObjectType' => $filterObjectType, 'filterObjectId' => $filterObjectId));

        $this->assign("logs", $logData);
        $this->assign("users", $users);

        $this->assign('allLogActions', LogHelper::getLogActions($this->getDatabase()));
        $this->assign('allObjectTypes', LogHelper::getObjectTypes());

        $this->setTemplate("logs/main.tpl");
    }
}

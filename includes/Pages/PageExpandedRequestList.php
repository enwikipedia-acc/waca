<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\RequestQueue;
use Waca\Fragments\RequestListData;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\RequestStatus;
use Waca\SiteConfiguration;
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
        if (WebRequest::getString('queue') === null) {
            $this->redirect('');
            return;
        }

        $database = $this->getDatabase();

        // FIXME: domains
        $queue = RequestQueue::getByApiName($database, WebRequest::getString('queue'), 1);

        if ($queue === false) {
            $this->redirect('');
            return;
        }

        /** @var SiteConfiguration $config */
        $config = $this->getSiteConfiguration();

        $this->assignCSRFToken();

        $this->assign('queuehelp', $queue->getHelp());

        // FIXME: domains
        $search = RequestSearchHelper::get($database, 1);
        $search->byStatus(RequestStatus::OPEN);

        list($defaultSort, $defaultSortDirection) = WebRequest::requestListDefaultSort();
        $this->assign('defaultSort', $defaultSort);
        $this->assign('defaultSortDirection', $defaultSortDirection);

        if ($config->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }

        $queuesById = [$queue->getId() => $queue];
        $requestsByQueue = $search->fetchByQueue(array_keys($queuesById));
        $requestData = $requestsByQueue[$queue->getId()];

        $this->assign('requests', $this->prepareRequestData($requestData['data']));
        $this->assign('totalRequests', $requestData['count']);
        $this->assign('header', $queue->getHeader());
        $this->assign('requestLimitShowOnly', $config->getMiserModeLimit());

        $this->setHtmlTitle('{$header|escape}{if $totalRequests > 0} [{$totalRequests|escape}]{/if}');
        $this->setTemplate('mainpage/expandedrequestlist.tpl');
    }
}

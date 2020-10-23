<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\Helpers\RequestQueueHelper;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageQueueManagement extends InternalPageBase
{
    protected function main()
    {
        $this->setHtmlTitle('Request Queue Management');

        $database = $this->getDatabase();
        $queues = RequestQueue::getAllQueues($database);

        $this->assign('queues', $queues);

        $user = User::getCurrent($database);
        $this->assign('canCreate', $this->barrierTest('create', $user));
        $this->assign('canEdit', $this->barrierTest('edit', $user));

        $this->setTemplate('queue-management/main.tpl');
    }

    protected function create()
    {
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $database = $this->getDatabase();

            $queue = new RequestQueue();

            $queue->setDatabase($database);
            $queue->setDomain(1); // FIXME: domain

            $queue->setHeader(WebRequest::postString('header'));
            $queue->setDisplayName(WebRequest::postString('displayName'));
            $queue->setApiName(WebRequest::postString('apiName'));
            $queue->setEnabled(WebRequest::postBoolean('enabled'));
            $queue->setDefault(WebRequest::postBoolean('default') && WebRequest::postBoolean('enabled'));
            $queue->setDefaultAntispoof(WebRequest::postBoolean('antispoof') && WebRequest::postBoolean('enabled'));
            $queue->setDefaultTitleBlacklist(WebRequest::postBoolean('titleblacklist') && WebRequest::postBoolean('enabled'));
            $queue->setHelp(WebRequest::postString('help'));
            $queue->setLogName(WebRequest::postString('logName'));
            $queue->setLegacyStatus(WebRequest::postString('legacyStatus'));

            $proceed = true;

            if (RequestQueue::getByApiName($database, $queue->getApiName(), 1) !== false) {
                // FIXME: domain
                SessionAlert::error("The chosen API name is already in use. Please choose another.");
                $proceed = false;
            }

            if (RequestQueue::getByDisplayName($database, $queue->getDisplayName(), 1) !== false) {
                // FIXME: domain
                SessionAlert::error("The chosen target display name is already in use. Please choose another.");
                $proceed = false;
            }

            if (RequestQueue::getByHeader($database, $queue->getHeader(), 1) !== false) {
                // FIXME: domain
                SessionAlert::error("The chosen header is already in use. Please choose another.");
                $proceed = false;
            }

            if ($proceed) {
                $queue->save();
                $this->redirect('queueManagement');
            }
            else {
                $this->populateFromObject($queue);

                $this->assign('createMode', true);
                $this->setTemplate('queue-management/edit.tpl');
            }
        }
        else {
            $this->assign('header', null);
            $this->assign('displayName', null);
            $this->assign('apiName', null);
            $this->assign('enabled', false);
            $this->assign('antispoof', false);
            $this->assign('titleblacklist', false);
            $this->assign('default', false);
            $this->assign('help', null);
            $this->assign('logName', null);
            $this->assign('legacyStatus', null);

            $this->assignCSRFToken();
            $this->assign('createMode', true);
            $this->setTemplate('queue-management/edit.tpl');
        }
    }

    protected function edit()
    {
        $database = $this->getDatabase();

        /** @var RequestQueue $queue */
        $queue = RequestQueue::getById(WebRequest::getInt('queue'), $database);

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $helper = new RequestQueueHelper();

            $helper->configureDefaults(
                $queue,
                WebRequest::postBoolean('enabled'),
                WebRequest::postBoolean('default'),
                WebRequest::postBoolean('antispoof'),
                WebRequest::postBoolean('titleblacklist'));

            $queue->setHeader(WebRequest::postString('header'));
            $queue->setDisplayName(WebRequest::postString('displayName'));
            $queue->setHelp(WebRequest::postString('help'));

            $proceed = true;

            $foundRequestQueue = RequestQueue::getByDisplayName($database, $queue->getDisplayName(), 1);
            if ($foundRequestQueue !== false && $foundRequestQueue->getId() !== $queue->getId()) {
                // FIXME: domain
                SessionAlert::error("The chosen target display name is already in use. Please choose another.");
                $proceed = false;
            }

            $foundRequestQueue = RequestQueue::getByHeader($database, $queue->getHeader(), 1);
            if ($foundRequestQueue !== false && $foundRequestQueue->getId() !== $queue->getId()) {
                // FIXME: domain
                SessionAlert::error("The chosen header is already in use. Please choose another.");
                $proceed = false;
            }

            if ($proceed) {
                $queue->save();
                $this->redirect('queueManagement');
            }
            else {
                $this->populateFromObject($queue);

                $this->assign('createMode', false);
                $this->setTemplate('queue-management/edit.tpl');
            }
        }
        else {
            $this->populateFromObject($queue);

            $this->assign('createMode', false);
            $this->setTemplate('queue-management/edit.tpl');
        }
    }

    /**
     * @param RequestQueue $queue
     */
    protected function populateFromObject(RequestQueue $queue): void
    {
        $this->assignCSRFToken();

        $this->assign('header', $queue->getHeader());
        $this->assign('displayName', $queue->getDisplayName());
        $this->assign('apiName', $queue->getApiName());
        $this->assign('enabled', $queue->isEnabled());
        $this->assign('default', $queue->isDefault());
        $this->assign('antispoof', $queue->isDefaultAntispoof());
        $this->assign('titleblacklist', $queue->isDefaultTitleBlacklist());
        $this->assign('help', $queue->getHelp());
        $this->assign('logName', $queue->getLogName());
        $this->assign('legacyStatus', $queue->getLegacyStatus());
    }
}
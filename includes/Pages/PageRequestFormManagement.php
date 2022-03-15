<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\RequestForm;
use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\Helpers\Logger;
use Waca\Helpers\RenderingHelper;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageRequestFormManagement extends InternalPageBase
{
    protected function main()
    {
        $this->setHtmlTitle('Request Form Management');

        $database = $this->getDatabase();
        $forms = RequestForm::getAllForms($database, 1); // FIXME: domains
        $this->assign('forms', $forms);

        $queues = [];
        foreach ($forms as $f) {
            $queueId = $f->getOverrideQueue();
            if ($queueId !== null) {
                if (!isset($queues[$queueId])) {
                    /** @var RequestQueue $queue */
                    $queue = RequestQueue::getById($queueId, $this->getDatabase());

                    if ($queue->getDomain() == 1) { // FIXME: domains
                        $queues[$queueId] = $queue;
                    }
                }
            }
        }

        $this->assign('queues', $queues);

        $user = User::getCurrent($database);
        $this->assign('canCreate', $this->barrierTest('create', $user));
        $this->assign('canEdit', $this->barrierTest('edit', $user));
        $this->assign('canView', $this->barrierTest('view', $user));

        $this->setTemplate('form-management/main.tpl');
    }

    protected function preview() {
        $previewContent = WebRequest::getSessionContext('preview');
        $renderer = new RenderingHelper();
        $this->assign('renderedContent', $renderer->doRender($previewContent));

        $this->setTemplate('form-management/preview.tpl');
    }

    protected function create()
    {
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $database = $this->getDatabase();

            $form = new RequestForm();

            $form->setDatabase($database);
            $form->setDomain(1); // FIXME: domain

            $form->setName(WebRequest::postString('name'));
            $form->setEnabled(WebRequest::postBoolean('enabled'));
            $form->setPublicEndpoint(WebRequest::postString('endpoint'));
            $form->setFormContent(WebRequest::postString('content'));
            $form->setOverrideQueue(WebRequest::postInt('queue'));

            if (WebRequest::postString("preview") === "preview") {
                $this->populateFromObject($form);

                WebRequest::setSessionContext('preview', $form->getFormContent());

                $this->assign('createMode', false);
                $this->setTemplate('form-management/edit.tpl');

                return;
            }

            $proceed = true;

            if (RequestForm::getByPublicEndpoint($database, $form->getPublicEndpoint()) !== false) {
                SessionAlert::error("The chosen public endpoint is already in use. Please choose another.");
                $proceed = false;
            }

            if (preg_match('^[A-Za-z][a-zA-Z0-9-]*$', $form->getPublicEndpoint()) !== 1) {
                SessionAlert::error("The chosen public endpoint contains invalid characters");
                //$proceed = false;
            }

            if (RequestForm::getByName($database, $form->getName(), 1) !== false) {
                // FIXME: domain
                SessionAlert::error("The chosen name is already in use. Please choose another.");
                $proceed = false;
            }

            if ($form->getOverrideQueue() !== null) {
                /** @var RequestQueue $queue */
                $queue = RequestQueue::getById($form->getOverrideQueue(), $database);
                if ($queue === false || $queue->getDomain() !== 1 || !$queue->isEnabled()) {
                    // FIXME: domain
                    SessionAlert::error("The chosen queue does not exist or is disabled.");
                    $proceed = false;
                }
            }

            if ($proceed) {
                $form->save();
                Logger::requestFormCreated($database, $form);
                $this->redirect('requestFormManagement');
            }
            else {
                $this->populateFromObject($form);
                WebRequest::setSessionContext('preview', $form->getFormContent());

                $this->assign('createMode', true);
                $this->setTemplate('form-management/edit.tpl');
            }
        }
        else {
            $this->populateFromObject(new RequestForm());
            WebRequest::setSessionContext('preview', '');

            $this->assignCSRFToken();
            $this->assign('createMode', true);
            $this->setTemplate('form-management/edit.tpl');
        }
    }

    protected function view()
    {
        $database = $this->getDatabase();

        /** @var RequestForm $form */
        $form = RequestForm::getById(WebRequest::getInt('form'), $database);
        // FIXME: domain check here

        $this->populateFromObject($form);

        if ($form->getOverrideQueue() !== null) {
            $this->assign('queueObject', RequestQueue::getById($form->getOverrideQueue(), $database));
        }

        WebRequest::setSessionContext('preview', $form->getFormContent());

        $renderer = new RenderingHelper();
        $this->assign('renderedContent', $renderer->doRender($form->getFormContent()));

        $this->setTemplate('form-management/view.tpl');
    }

    protected function edit()
    {
        $database = $this->getDatabase();

        /** @var RequestForm $form */
        $form = RequestForm::getById(WebRequest::getInt('form'), $database);
        // FIXME: domain check here

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $form->setName(WebRequest::postString('name'));
            $form->setFormContent(WebRequest::postString('content'));
            $form->setOverrideQueue(WebRequest::postInt('queue'));
            $form->setEnabled(WebRequest::postBoolean('enabled'));

            if (WebRequest::postString("preview") === "preview") {
                $this->populateFromObject($form);

                WebRequest::setSessionContext('preview', $form->getFormContent());

                $this->assign('createMode', false);
                $this->setTemplate('form-management/edit.tpl');

                return;
            }

            $proceed = true;

            $foundForm = RequestForm::getByName($database, $form->getName(), 1);
            if ($foundForm !== false && $foundForm->getId() !== $form->getId()) {
                // FIXME: domain
                SessionAlert::error("The chosen name is already in use. Please choose another.");
                $proceed = false;
            }

            if ($form->getOverrideQueue() !== null) {
                /** @var RequestQueue $queue */
                $queue = RequestQueue::getById($form->getOverrideQueue(), $database);
                if ($queue === false || $queue->getDomain() !== 1 || !$queue->isEnabled()) {
                    // FIXME: domain
                    SessionAlert::error("The chosen queue does not exist or is disabled.");
                    $proceed = false;
                }
            }

            if ($proceed) {
                Logger::requestFormEdited($database, $form);
                $form->save();
                $this->redirect('requestFormManagement');
            }
            else {
                $this->populateFromObject($form);
                WebRequest::setSessionContext('preview', $form->getFormContent());

                $this->assign('createMode', false);
                $this->setTemplate('form-management/edit.tpl');
            }
        }
        else {
            $this->populateFromObject($form);
            WebRequest::setSessionContext('preview', $form->getFormContent());

            $this->assign('createMode', false);
            $this->setTemplate('form-management/edit.tpl');
        }
    }

    /**
     * @param RequestForm $form
     */
    protected function populateFromObject(RequestForm $form): void
    {
        $this->assignCSRFToken();

        $this->assign('name', $form->getName());
        $this->assign('enabled', $form->isEnabled());
        $this->assign('endpoint', $form->getPublicEndpoint());
        $this->assign('queue', $form->getOverrideQueue());
        $this->assign('content', $form->getFormContent());

        $this->assign('availableQueues', RequestQueue::getEnabledQueues($this->getDatabase()));
    }
}

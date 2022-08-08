<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Domain;
use Waca\DataObjects\RequestForm;
use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\Helpers\MarkdownRenderingHelper;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageRequestFormManagement extends InternalPageBase
{
    protected function main()
    {
        $this->setHtmlTitle('Request Form Management');

        $database = $this->getDatabase();
        $domainId = Domain::getCurrent($database)->getId();
        $forms = RequestForm::getAllForms($database, $domainId);
        $this->assign('forms', $forms);

        $queues = [];
        foreach ($forms as $f) {
            $queueId = $f->getOverrideQueue();
            if ($queueId !== null) {
                if (!isset($queues[$queueId])) {
                    /** @var RequestQueue $queue */
                    $queue = RequestQueue::getById($queueId, $this->getDatabase());

                    if ($queue->getDomain() == $domainId) {
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

        $renderer = new MarkdownRenderingHelper();
        $this->assign('renderedContent', $renderer->doRender($previewContent['main']));
        $this->assign('username', $renderer->doRenderInline($previewContent['username']));
        $this->assign('email', $renderer->doRenderInline($previewContent['email']));
        $this->assign('comment', $renderer->doRenderInline($previewContent['comment']));

        $this->setTemplate('form-management/preview.tpl');
    }

    protected function create()
    {
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $database = $this->getDatabase();
            $domainId = Domain::getCurrent($database)->getId();

            $form = new RequestForm();

            $form->setDatabase($database);
            $form->setDomain($domainId);

            $this->setupObjectFromPost($form);
            $form->setPublicEndpoint(WebRequest::postString('endpoint'));

            if (WebRequest::postString("preview") === "preview") {
                $this->populateFromObject($form);

                WebRequest::setSessionContext('preview', [
                    'main' => $form->getFormContent(),
                    'username' => $form->getUsernameHelp(),
                    'email' => $form->getEmailHelp(),
                    'comment' => $form->getCommentHelp(),
                ]);

                $this->assign('createMode', true);
                $this->setTemplate('form-management/edit.tpl');

                return;
            }

            $proceed = true;

            if (RequestForm::getByPublicEndpoint($database, $form->getPublicEndpoint(), $domainId) !== false) {
                SessionAlert::error("The chosen public endpoint is already in use. Please choose another.");
                $proceed = false;
            }

            if (preg_match('/^[A-Za-z][a-zA-Z0-9-]*$/', $form->getPublicEndpoint()) !== 1) {
                SessionAlert::error("The chosen public endpoint contains invalid characters");
                $proceed = false;
            }

            if (RequestForm::getByName($database, $form->getName(), $domainId) !== false) {
                SessionAlert::error("The chosen name is already in use. Please choose another.");
                $proceed = false;
            }

            if ($form->getOverrideQueue() !== null) {
                /** @var RequestQueue|bool $queue */
                $queue = RequestQueue::getById($form->getOverrideQueue(), $database);
                if ($queue === false || $queue->getDomain() !== $domainId || !$queue->isEnabled()) {
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
                WebRequest::setSessionContext('preview', [
                    'main' => $form->getFormContent(),
                    'username' => $form->getUsernameHelp(),
                    'email' => $form->getEmailHelp(),
                    'comment' => $form->getCommentHelp(),
                ]);

                $this->assign('createMode', true);
                $this->setTemplate('form-management/edit.tpl');
            }
        }
        else {
            $this->populateFromObject(new RequestForm());
            WebRequest::setSessionContext('preview', null);
            $this->assign('hidePreview', true);

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

        if ($form->getDomain() !== Domain::getCurrent($database)->getId()) {
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        $this->populateFromObject($form);

        if ($form->getOverrideQueue() !== null) {
            $this->assign('queueObject', RequestQueue::getById($form->getOverrideQueue(), $database));
        }

        WebRequest::setSessionContext('preview', [
            'main' => $form->getFormContent(),
            'username' => $form->getUsernameHelp(),
            'email' => $form->getEmailHelp(),
            'comment' => $form->getCommentHelp(),
        ]);

        $renderer = new MarkdownRenderingHelper();
        $this->assign('renderedContent', $renderer->doRender($form->getFormContent()));

        $this->setTemplate('form-management/view.tpl');
    }

    protected function edit()
    {
        $database = $this->getDatabase();

        /** @var RequestForm $form */
        $form = RequestForm::getById(WebRequest::getInt('form'), $database);

        if ($form->getDomain() !== Domain::getCurrent($database)->getId()) {
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $this->setupObjectFromPost($form);

            if (WebRequest::postString("preview") === "preview") {
                $this->populateFromObject($form);

                WebRequest::setSessionContext('preview', [
                    'main' => $form->getFormContent(),
                    'username' => $form->getUsernameHelp(),
                    'email' => $form->getEmailHelp(),
                    'comment' => $form->getCommentHelp(),
                ]);

                $this->assign('createMode', false);
                $this->setTemplate('form-management/edit.tpl');

                return;
            }

            $proceed = true;

            $foundForm = RequestForm::getByName($database, $form->getName(), $form->getDomain());
            if ($foundForm !== false && $foundForm->getId() !== $form->getId()) {
                SessionAlert::error("The chosen name is already in use. Please choose another.");
                $proceed = false;
            }

            if ($form->getOverrideQueue() !== null) {
                /** @var RequestQueue $queue */
                $queue = RequestQueue::getById($form->getOverrideQueue(), $database);
                if ($queue === false || $queue->getDomain() !== $form->getDomain() || !$queue->isEnabled()) {
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
                WebRequest::setSessionContext('preview', [
                    'main' => $form->getFormContent(),
                    'username' => $form->getUsernameHelp(),
                    'email' => $form->getEmailHelp(),
                    'comment' => $form->getCommentHelp(),
                ]);

                $this->assign('createMode', false);
                $this->setTemplate('form-management/edit.tpl');
            }
        }
        else {
            $this->populateFromObject($form);
            WebRequest::setSessionContext('preview', [
                'main' => $form->getFormContent(),
                'username' => $form->getUsernameHelp(),
                'email' => $form->getEmailHelp(),
                'comment' => $form->getCommentHelp(),
            ]);

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
        $this->assign('username', $form->getUsernameHelp());
        $this->assign('email', $form->getEmailHelp());
        $this->assign('comment', $form->getCommentHelp());

        $this->assign('domain', $form->getDomainObject());

        $this->assign('availableQueues', RequestQueue::getEnabledQueues($this->getDatabase()));
    }

    /**
     * @param RequestForm $form
     *
     * @return void
     * @throws ApplicationLogicException
     */
    protected function setupObjectFromPost(RequestForm $form): void
    {
        if (WebRequest::postString('content') === null
            || WebRequest::postString('username') === null
            || WebRequest::postString('email') === null
            || WebRequest::postString('comment') === null
        ) {
            throw new ApplicationLogicException("Form content, username help, email help, and comment help are all required fields.");
        }

        $form->setName(WebRequest::postString('name'));
        $form->setEnabled(WebRequest::postBoolean('enabled'));
        $form->setFormContent(WebRequest::postString('content'));
        $form->setOverrideQueue(WebRequest::postInt('queue'));
        $form->setUsernameHelp(WebRequest::postString('username'));
        $form->setEmailHelp(WebRequest::postString('email'));
        $form->setCommentHelp(WebRequest::postString('comment'));
    }
}

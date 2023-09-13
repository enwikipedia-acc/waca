<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Domain;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageEmailManagement extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->setHtmlTitle('Close Emails');

        $domain = Domain::getCurrent($this->getDatabase());

        // Get all active email templates
        $activeTemplates = EmailTemplate::getAllActiveTemplates(null, $this->getDatabase(), $domain->getId());
        $inactiveTemplates = EmailTemplate::getAllInactiveTemplates($this->getDatabase(), $domain->getId());

        $this->assign('activeTemplates', $activeTemplates);
        $this->assign('inactiveTemplates', $inactiveTemplates);

        $user = User::getCurrent($this->getDatabase());
        $this->assign('canCreate', $this->barrierTest('create', $user));
        $this->assign('canEdit', $this->barrierTest('edit', $user));

        $this->setTemplate('email-management/main.tpl');
    }

    protected function view()
    {
        $this->setHtmlTitle('Close Emails');

        $database = $this->getDatabase();
        $template = $this->getTemplate($database);

        /** @var Domain $domain */
        $domain = Domain::getCurrent($database);

        $this->assign('id', $template->getId());
        $this->assign('emailTemplate', $template);
        $this->assign('createdid', $domain->getDefaultClose());

        $this->setTemplate('email-management/view.tpl');
    }

    /**
     * @param PdoDatabase $database
     *
     * @return EmailTemplate
     * @throws ApplicationLogicException
     */
    protected function getTemplate(PdoDatabase $database)
    {
        $templateId = WebRequest::getInt('id');
        if ($templateId === null) {
            throw new ApplicationLogicException('Template not specified');
        }
        $template = EmailTemplate::getById($templateId, $database);
        if ($template === false || !is_a($template, EmailTemplate::class)) {
            throw new ApplicationLogicException('Template not found');
        }

        return $template;
    }

    protected function edit()
    {
        $this->setHtmlTitle('Close Emails');

        $database = $this->getDatabase();
        $template = $this->getTemplate($database);

        /** @var Domain $domain */
        $domain = Domain::getCurrent($database);

        $createdId = $domain->getDefaultClose();

        $requestQueues = RequestQueue::getEnabledQueues($database, $domain->getId());

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $this->modifyTemplateData($template);

            $other = EmailTemplate::getByName($template->getName(), $database, $domain->getId());
            if ($other !== false && $other->getId() !== $template->getId()) {
                throw new ApplicationLogicException('A template with this name already exists');
            }

            if ($template->getId() === $createdId) {
                $template->setDefaultAction(EmailTemplate::ACTION_CREATED);
                $template->setActive(true);
                $template->setPreloadOnly(false);
            }

            // optimistically lock on load of edit form
            $updateVersion = WebRequest::postInt('updateversion');
            $template->setUpdateVersion($updateVersion);

            $template->save();
            Logger::editedEmail($database, $template);
            $this->getNotificationHelper()->emailEdited($template);
            SessionAlert::success("Email template has been saved successfully.");

            $this->redirect('emailManagement');
        }
        else {
            $this->assignCSRFToken();
            $this->assign('id', $template->getId());
            $this->assign('emailTemplate', $template);
            $this->assign('createdid', $createdId);
            $this->assign('requestQueues', $requestQueues);

            $this->setTemplate('email-management/edit.tpl');
        }
    }

    /**
     * @param EmailTemplate $template
     *
     * @throws ApplicationLogicException
     */
    private function modifyTemplateData(EmailTemplate $template)
    {
        $name = WebRequest::postString('name');
        if ($name === null || $name === '') {
            throw new ApplicationLogicException('Name not specified');
        }

        $template->setName($name);

        $text = WebRequest::postString('text');
        if ($text === null || $text === '') {
            throw new ApplicationLogicException('Text not specified');
        }

        $template->setText($text);

        $jsquestion = WebRequest::postString('jsquestion');
        if ($jsquestion === null || $jsquestion === '') {
            throw new ApplicationLogicException('JS question not specified');
        }
        $template->setJsquestion($jsquestion);

        switch (WebRequest::postString('defaultaction')) {
            case EmailTemplate::ACTION_NONE:
            case EmailTemplate::ACTION_CREATED:
            case EmailTemplate::ACTION_NOT_CREATED:
                $template->setDefaultAction(WebRequest::postString('defaultaction'));
                $template->setQueue(null);
                break;
            default:
                $template->setDefaultAction(EmailTemplate::ACTION_DEFER);
                $database = $this->getDatabase();
                $domain = Domain::getCurrent($database);
                $requestQueue = RequestQueue::getByApiName($database, WebRequest::postString('defaultaction'), $domain->getId());
                if ($requestQueue === false) {
                    throw new ApplicationLogicException("Cannot find request queue specified");
                }

                $template->setQueue($requestQueue->getId());
                break;
        }

        $template->setActive(WebRequest::postBoolean('active'));
        $template->setPreloadOnly(WebRequest::postBoolean('preloadonly'));
    }

    protected function create()
    {
        $this->setHtmlTitle('Close Emails');

        $database = $this->getDatabase();
        $domain = Domain::getCurrent($database);

        $requestQueues = RequestQueue::getEnabledQueues($database,$domain->getId());

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $template = new EmailTemplate();
            $template->setDatabase($database);

            $template->setDomain($domain->getId());

            $this->modifyTemplateData($template);

            $other = EmailTemplate::getByName($template->getName(), $database, $template->getDomain());
            if ($other !== false) {
                throw new ApplicationLogicException('A template with this name already exists');
            }

            $template->save();

            Logger::createEmail($database, $template);
            $this->getNotificationHelper()->emailCreated($template);

            SessionAlert::success("Email template has been saved successfully.");

            $this->redirect('emailManagement');
        }
        else {
            $this->assignCSRFToken();
            $this->assign('id', -1);
            $this->assign('emailTemplate', new EmailTemplate());
            $this->assign('createdid', -2);

            $this->assign('requestQueues', $requestQueues);
            $this->setTemplate('email-management/edit.tpl');
        }
    }
}

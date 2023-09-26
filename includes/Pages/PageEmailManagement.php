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

        // Get all active email templates
        // FIXME: domains!
        $activeTemplates = EmailTemplate::getAllActiveTemplates(null, $this->getDatabase(), 1);
        $inactiveTemplates = EmailTemplate::getAllInactiveTemplates($this->getDatabase(), 1);

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

        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $database);

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

        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $database);

        $createdId = $domain->getDefaultClose();

        $requestQueues = RequestQueue::getEnabledQueues($database);

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $this->modifyTemplateData($template, $template->getId() === $createdId);

            $other = EmailTemplate::getByName($template->getName(), $database, $domain->getId());
            if ($other !== false && $other->getId() !== $template->getId()) {
                throw new ApplicationLogicException('A template with this name already exists');
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
     * @throws ApplicationLogicException
     */
    private function modifyTemplateData(EmailTemplate $template, bool $isDefaultTemplate): void
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

        if ($isDefaultTemplate) {
            $template->setDefaultAction(EmailTemplate::ACTION_CREATED);
            $template->setActive(true);
            $template->setPreloadOnly(false);
        }
        else {
            $defaultAction = WebRequest::postString('defaultaction');
            switch ($defaultAction) {
                case EmailTemplate::ACTION_NONE:
                case EmailTemplate::ACTION_CREATED:
                case EmailTemplate::ACTION_NOT_CREATED:
                    $template->setDefaultAction($defaultAction);
                    $template->setQueue(null);
                    break;
                default:
                    $template->setDefaultAction(EmailTemplate::ACTION_DEFER);
                    // FIXME: domains!
                    $queue = RequestQueue::getByApiName($this->getDatabase(), $defaultAction, 1);
                    $template->setQueue($queue->getId());
                    break;
            }

            $template->setActive(WebRequest::postBoolean('active'));
            $template->setPreloadOnly(WebRequest::postBoolean('preloadonly'));
        }
    }

    protected function create()
    {
        $this->setHtmlTitle('Close Emails');

        $database = $this->getDatabase();

        $requestQueues = RequestQueue::getEnabledQueues($database);

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $template = new EmailTemplate();
            $template->setDatabase($database);

            // FIXME: domains!
            $template->setDomain(1);

            $this->modifyTemplateData($template, false);

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

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\EmailTemplate;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageEmailManagement extends InternalPageBase
{
    /**
     * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
     * the return value from this function.
     *
     * If this page even supports actions, you will need to check the route
     *
     * @return \Waca\Security\SecurityConfiguration
     * @category Security-Critical
     */
    protected function getSecurityConfiguration()
    {
        switch ($this->getRouteName()) {
            case 'edit':
            case 'create':
                return $this->getSecurityManager()->configure()->asAdminPage();
            case 'view':
            case 'main':
                return $this->getSecurityManager()->configure()->asInternalPage();
        }

        // deny all
        return $this->getSecurityManager()->configure()->asNone();
    }

    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->setHtmlTitle('Close Emails');

        // Get all active email templates
        $activeTemplates = EmailTemplate::getAllActiveTemplates(null, $this->getDatabase());
        $inactiveTemplates = EmailTemplate::getAllInactiveTemplates($this->getDatabase());

        $this->assign('activeTemplates', $activeTemplates);
        $this->assign('inactiveTemplates', $inactiveTemplates);

        $this->setTemplate('email-management/main.tpl');
    }

    protected function view()
    {
        $this->setHtmlTitle('Close Emails');

        $database = $this->getDatabase();
        $template = $this->getTemplate($database);

        $createdId = $this->getSiteConfiguration()->getDefaultCreatedTemplateId();
        $requestStates = $this->getSiteConfiguration()->getRequestStates();

        $this->assign('id', $template->getId());
        $this->assign('emailTemplate', $template);
        $this->assign('createdid', $createdId);
        $this->assign('requeststates', $requestStates);

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

        $createdId = $this->getSiteConfiguration()->getDefaultCreatedTemplateId();
        $requestStates = $this->getSiteConfiguration()->getRequestStates();

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $this->modifyTemplateData($template);

            $other = EmailTemplate::getByName($template->getName(), $database);
            if ($other !== false && $other->getId() !== $template->getId()) {
                throw new ApplicationLogicException('A template with this name already exists');
            }

            if ($template->getId() === $createdId) {
                $template->setDefaultAction(EmailTemplate::CREATED);
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
            $this->assign('requeststates', $requestStates);

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

        $template->setJsquestion(WebRequest::postString('jsquestion'));

        $template->setDefaultAction(WebRequest::postString('defaultaction'));
        $template->setActive(WebRequest::postBoolean('active'));
        $template->setPreloadOnly(WebRequest::postBoolean('preloadonly'));
    }

    protected function create()
    {
        $this->setHtmlTitle('Close Emails');

        $database = $this->getDatabase();

        $requestStates = $this->getSiteConfiguration()->getRequestStates();

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $template = new EmailTemplate();
            $template->setDatabase($database);

            $this->modifyTemplateData($template);

            $other = EmailTemplate::getByName($template->getName(), $database);
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
            $this->assign('requeststates', $requestStates);
            $this->setTemplate('email-management/create.tpl');
        }
    }
}
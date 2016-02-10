<?php

namespace Waca\Pages;

use EmailTemplate;
use Logger;
use Notification;
use PdoDatabase;
use SessionAlert;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageEmailManagement extends PageBase
{
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
		switch ($this->getRouteName()) {
			case 'edit':
			case 'create':
				return SecurityConfiguration::adminPage();
			case 'view':
			case 'main':
				return SecurityConfiguration::internalPage();
		}

		// deny all
		return new SecurityConfiguration();
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		// Get all active email templates
		$activeTemplates = EmailTemplate::getAllActiveTemplates();
		$inactiveTemplates = EmailTemplate::getAllInactiveTemplates();

		$this->assign('activeTemplates', $activeTemplates);
		$this->assign('inactiveTemplates', $inactiveTemplates);

		$this->setTemplate('email-management/main.tpl');
	}

	protected function view()
	{
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
		if (!is_a($template, EmailTemplate::class)) {
			throw new ApplicationLogicException('Template not found');
		}

		return $template;
	}

	protected function edit()
	{
		$database = $this->getDatabase();
		$template = $this->getTemplate($database);

		$createdId = $this->getSiteConfiguration()->getDefaultCreatedTemplateId();
		$requestStates = $this->getSiteConfiguration()->getRequestStates();

		if (WebRequest::wasPosted()) {
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

			$template->save();
			Logger::editedEmail($database, $template);
			Notification::emailEdited($template);
			SessionAlert::success("Email template has been saved successfully.");

			$this->redirect('emailManagement');
		}
		else {
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
		$database = $this->getDatabase();

		$requestStates = $this->getSiteConfiguration()->getRequestStates();

		if (WebRequest::wasPosted()) {
			$template = new EmailTemplate();
			$template->setDatabase($database);

			$this->modifyTemplateData($template);

			$other = EmailTemplate::getByName($template->getName(), $database);
			if ($other !== false) {
				throw new ApplicationLogicException('A template with this name already exists');
			}

			$template->save();

			Logger::createEmail($database, $template);
			Notification::emailCreated($template);

			SessionAlert::success("Email template has been saved successfully.");

			$this->redirect('emailManagement');
		}
		else {
			$this->assign('requeststates', $requestStates);
			$this->setTemplate('email-management/create.tpl');
		}
	}
}
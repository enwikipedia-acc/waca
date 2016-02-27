<?php

namespace Waca\Pages;

use Exception;
use User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\SecurityConfiguration;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;
use WelcomeTemplate;

class PageWelcomeTemplateManagement extends InternalPageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		$templateList = WelcomeTemplate::getAll($this->getDatabase());

		$this->assign('templateList', $templateList);
		$this->setTemplate('welcome-template/list.tpl');
	}

	/**
	 * Handles the requests for selecting a template to use.
	 *
	 * @throws ApplicationLogicException
	 */
	protected function select()
	{
		// get rid of GETs
		if (!WebRequest::wasPosted()) {
			$this->redirect('welcomeTemplates');
		}

		$user = User::getCurrent($this->getDatabase());

		if (WebRequest::postBoolean('disable')) {
			$user->setWelcomeTemplate(null);
			$user->save();

			SessionAlert::success('Disabled automatic user welcoming.');
			$this->redirect('welcomeTemplates');

			return;
		}

		$database = $this->getDatabase();

		$templateId = WebRequest::postInt('template');
		$template = WelcomeTemplate::getById($templateId, $database);

		if ($template === false) {
			throw new ApplicationLogicException('Unknown template');
		}

		$user->setWelcomeTemplate($template->getId());
		$user->save();

		SessionAlert::success("Updated selected welcome template for automatic welcoming.");

		$this->redirect('welcomeTemplates');
	}

	/**
	 * Handles the requests for viewing a template.
	 *
	 * @throws ApplicationLogicException
	 */
	protected function view()
	{
		$database = $this->getDatabase();

		$templateId = WebRequest::getInt('template');

		/** @var WelcomeTemplate $template */
		$template = WelcomeTemplate::getById($templateId, $database);

		if ($template === false) {
			throw new ApplicationLogicException('Cannot find requested template');
		}

		$templateHtml = $this->getWikiTextHelper()->getHtmlForWikiText($template->getBotCode());

		$this->assign('templateHtml', $templateHtml);
		$this->assign('template', $template);
		$this->setTemplate('welcome-template/view.tpl');
	}

	/**
	 * Handler for the add action to create a new welcome template
	 *
	 * @throws Exception
	 */
	protected function add()
	{
		if (WebRequest::wasPosted()) {
			$database = $this->getDatabase();

			$template = new WelcomeTemplate();
			$template->setDatabase($database);
			$template->setUserCode(WebRequest::postString('usercode'));
			$template->setBotCode(WebRequest::postString('botcode'));
			$template->save();

			Logger::welcomeTemplateCreated($database, $template);

			$this->getNotificationHelper()->welcomeTemplateCreated($template);

			SessionAlert::success("Template successfully created.");

			$this->redirect('welcomeTemplates');
		}
		else {
			$this->setTemplate("welcome-template/add.tpl");
		}
	}

	/**
	 * Hander for editing templates
	 */
	protected function edit()
	{
		$database = $this->getDatabase();

		$templateId = WebRequest::getInt('template');

		/** @var WelcomeTemplate $template */
		$template = WelcomeTemplate::getById($templateId, $database);

		if ($template === false) {
			throw new ApplicationLogicException('Cannot find requested template');
		}

		if (WebRequest::wasPosted()) {
			$template->setUserCode(WebRequest::postString('usercode'));
			$template->setBotCode(WebRequest::postString('botcode'));
			$template->save();

			Logger::welcomeTemplateEdited($database, $template);

			SessionAlert::success("Template updated.");

			$this->getNotificationHelper()->welcomeTemplateEdited($template);

			$this->redirect('welcomeTemplates');
		}
		else {
			$this->assign('template', $template);
			$this->setTemplate('welcome-template/edit.tpl');
		}
	}

	protected function delete()
	{
		$this->redirect('welcomeTemplates');

		if (!WebRequest::wasPosted()) {
			return;
		}

		$database = $this->getDatabase();

		$templateId = WebRequest::postInt('template');

		/** @var WelcomeTemplate $template */
		$template = WelcomeTemplate::getById($templateId, $database);

		if ($template === false) {
			throw new ApplicationLogicException('Cannot find requested template');
		}

		$database
			->prepare("UPDATE user SET welcome_template = NULL WHERE welcome_template = :id;")
			->execute(array(":id" => $templateId));

		Logger::welcomeTemplateDeleted($database, $template);

		$template->delete();

		SessionAlert::success(
			"Template deleted. Any users who were using this template have had automatic welcoming disabled.");
		$this->getNotificationHelper()->welcomeTemplateDeleted($templateId);
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
		switch ($this->getRouteName()) {
			case 'edit':
			case 'add':
			case 'delete':
				// WARNING: if you want to unlink edit/add/delete, you'll want to change the barrier tests in the
				// template
				return SecurityConfiguration::adminPage();
			case 'view':
			case 'select':
				return SecurityConfiguration::internalPage();
			default:
				return SecurityConfiguration::internalPage();
		}
	}
}
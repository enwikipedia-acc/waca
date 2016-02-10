<?php

namespace Waca\Pages;

use InterfaceMessage;
use Logger;
use Notification;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageInterfaceManagement extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		$database = $this->getDatabase();

		/** @var InterfaceMessage $siteNoticeMessage */
		$siteNoticeMessage = InterfaceMessage::getById(InterfaceMessage::SITENOTICE, $database);

		// Dual-mode
		if (WebRequest::wasPosted()) {
			$siteNoticeMessage->setContent(WebRequest::postString('mailtext'));
			$siteNoticeMessage->save();

			Logger::interfaceMessageEdited($database, $siteNoticeMessage);
			Notification::interfaceMessageEdited();

			$this->redirect('');
		}
		else {
			$this->setTemplate('interface-management/editform.tpl');
			$this->assign('message', $siteNoticeMessage);
		}
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
		return SecurityConfiguration::adminPage();
	}
}
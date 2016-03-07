<?php

namespace Waca\Pages;

use Exception;
use Waca\DataObjects\Ban;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\InterfaceMessage;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\DataObjects\WelcomeTemplate;
use Waca\Helpers\Logger;
use Waca\SecurityConfiguration;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageLog extends InternalPageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		$this->setHtmlTitle('Logs');

		$filterUser = WebRequest::getString('filterUser');
		$filterAction = WebRequest::getString('filterAction');

		$database = $this->getDatabase();

		$this->getTypeAheadHelper()->defineTypeAheadSource('username-typeahead', function() use ($database) {
			return User::getAllUsernames($database);
		});

		$limit = WebRequest::getInt('limit');
		if ($limit === null) {
			$limit = 100;
		}

		$page = WebRequest::getInt('page');
		if ($page === null) {
			$page = 1;
		}

		$offset = ($page - 1) * $limit;

		$logs = Logger::getLogs($database, $filterUser, $filterAction, $limit, $offset);

		if ($logs === false) {
			$this->assign('logs', array());
			$this->setTemplate('logs/main.tpl');

			return;
		}

		$count = $logs['count'];
		unset($logs['count']);

		$this->setupPageData($page, $limit, $count);

		$userIds = array();

		/** @var Log $logEntry */
		foreach ($logs as $logEntry) {
			if (!$logEntry instanceof Log) {
				// if this happens, we've done something wrong with passing back the log data.
				throw new Exception('Log entry is not an instance of a Log, this should never happen.');
			}

			$user = $logEntry->getUser();
			if ($user === -1) {
				continue;
			}

			if (!array_search($user, $userIds)) {
				$userIds[] = $user;
			}
		}

		$users = User::getUsernames($userIds, $database);
		$users[-1] = User::getCommunity()->getUsername();

		$logData = array();

		/** @var Log $logEntry */
		foreach ($logs as $logEntry) {
			$objectDescription = $this->getObjectDescription($logEntry->getObjectId(), $logEntry->getObjectType());

			$logData[] = array(
				'timestamp'         => $logEntry->getTimestamp(),
				'userid'            => $logEntry->getUser(),
				'username'          => $users[$logEntry->getUser()],
				'description'       => Logger::getLogDescription($logEntry),
				'objectdescription' => $objectDescription,
			);
		}

		$this->assign("logs", $logData);
		$this->assign("users", $users);

		$this->assign("filterUser", $filterUser);
		$this->assign("filterAction", $filterAction);

		$this->assign('allLogActions', Logger::getLogActions($this->getDatabase()));

		$this->setTemplate("logs/main.tpl");
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
		return SecurityConfiguration::internalPage();
	}

	/**
	 * @param $page
	 * @param $limit
	 * @param $count
	 */
	protected function setupPageData($page, $limit, $count)
	{
		// The number of pages on the pager to show. Must be odd
		$pageLimit = 9;

		$pageData = array(
			// Can the user go to the previous page?
			'canprev'   => $page != 1,
			// Can the user go to the next page?
			'cannext'   => ($page * $limit) < $count,
			// Maximum page number
			'maxpage'   => ceil($count / $limit),
			// Limit to the number of pages to display
			'pagelimit' => $pageLimit,
		);

		// number of pages either side of the current to show
		$pageMargin = (($pageLimit - 1) / 2);

		// Calculate the number of pages either side to show - this is for situations like:
		//  [1]  [2] [[3]] [4]  [5]  [6]  [7]  [8]  [9] - where you can't just use the page margin calculated
		$pageData['lowpage'] = max(1, $page - $pageMargin);
		$pageData['hipage'] = min($pageData['maxpage'], $page + $pageMargin);
		$pageCount = ($pageData['hipage'] - $pageData['lowpage']) + 1;

		if ($pageCount < $pageLimit) {
			if ($pageData['lowpage'] == 1 && $pageData['hipage'] < $pageData['maxpage']) {
				$pageData['hipage'] = min($pageLimit, $pageData['maxpage']);
			}
			elseif ($pageData['lowpage'] > 1 && $pageData['hipage'] == $pageData['maxpage']) {
				$pageData['lowpage'] = max(1, $pageData['maxpage'] - $pageLimit + 1);
			}
		}

		// Put the range of pages into the page data
		$pageData['pages'] = range($pageData['lowpage'], $pageData['hipage']);

		$this->assign("pagedata", $pageData);

		$this->assign("limit", $limit);
		$this->assign("page", $page);
	}

	/**
	 * This returns a HTML
	 *
	 * @param string $objectId
	 * @param string $objectType
	 *
	 * @return string|null
	 * @category Security-Critical
	 */
	private function getObjectDescription($objectId, $objectType)
	{
		if ($objectType == '') {
			return null;
		}

		$database = $this->getDatabase();
		$baseurl = $this->getSiteConfiguration()->getBaseUrl();

		switch ($objectType) {
			case 'Ban':
				/** @var Ban $ban */
				$ban = Ban::getById($objectId, $database);

				return 'Ban #' . $objectId . " (" . htmlentities($ban->getTarget()) . ")</a>";
			case 'EmailTemplate':
				/** @var EmailTemplate $emailTemplate */
				$emailTemplate = EmailTemplate::getById($objectId, $database);
				$name = htmlentities($emailTemplate->getName(), ENT_COMPAT, 'UTF-8');

				return <<<HTML
<a href="{$baseurl}/internal.php/emailManagement/view?id={$objectId}">Email Template #{$objectId} ({$name})</a>
HTML;
			case 'InterfaceMessage':
				/** @var InterfaceMessage $interfaceMessage */
				$interfaceMessage = InterfaceMessage::getById($objectId, $database);
				$description = htmlentities($interfaceMessage->getDescription(), ENT_COMPAT, 'UTF-8');

				return "<a href=\"{$baseurl}/internal.php/siteNotice\">{$description}</a>";
			case 'Request':
				/** @var Request $request */
				$request = Request::getById($objectId, $database);
				$name = htmlentities($request->getName(), ENT_COMPAT, 'UTF-8');

				return <<<HTML
<a href="{$baseurl}/internal.php/viewRequest?id={$objectId}">Request #{$objectId} ({$name})</a>
HTML;
			case 'User':
				/** @var User $user */
				$user = User::getById($objectId, $database);
				$username = htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8');

				return "<a href=\"{$baseurl}/internal.php/statistics/users/detail?user={$objectId}\">{$username}</a>";
			case 'WelcomeTemplate':
				/** @var WelcomeTemplate $welcomeTemplate */
				$welcomeTemplate = WelcomeTemplate::getById($objectId, $database);
				$userCode = htmlentities($welcomeTemplate->getUserCode(), ENT_COMPAT, 'UTF-8');

				return "<a href=\"{$baseurl}/internal.php/welcomeTemplates/view?id={$objectId}\">{$userCode}</a>";
			default:
				return '[' . $objectType . " " . $objectId . ']';
				break;
		}
	}
}
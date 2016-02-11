<?php

namespace Waca\Pages;

use Log;
use Logger;
use User;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageLog extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		$filterUser = WebRequest::getString('filterUser');
		$filterAction = WebRequest::getString('filterAction');

		$database = $this->getDatabase();

		$this->getTypeAheadHelper()->defineTypeAheadSource('username-typeahead', function() use($database) {
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
		foreach($logs as $logEntry) {
			$user = $logEntry->getUser();
			if(!array_search($user, $userIds)){
				$userIds[] = $user;
			}
		}

		$users = User::getUsernames($userIds, $database);

		$this->assign("logs", $logs);
		$this->assign("users", $users);

		$this->assign("filterUser", $filterUser);
		$this->assign("filterAction", $filterAction);

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
}
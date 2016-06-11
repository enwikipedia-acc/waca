<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\Security\SecurityConfiguration;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageSearch extends InternalPageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		$this->setHtmlTitle('Search');

		// Dual-mode page
		if (WebRequest::wasPosted()) {
			$this->validateCSRFToken();
			// TODO: logging on private data access

			$searchType = WebRequest::postString('type');
			$searchTerm = WebRequest::postString('term');

			$validationError = "";
			if(!$this->validateSearchParameters($searchType, $searchTerm, $validationError)) {
				SessionAlert::error($validationError, "Search error");
				$this->redirect("search");
				return;
			}

			$results = array();

			switch ($searchType) {
				case 'name':
					$results = $this->getNameSearchResults($searchTerm);
					break;
				case 'email':
					$results = $this->getEmailSearchResults($searchTerm);
					break;
				case 'ip':
					$results = $this->getIpSearchResults($searchTerm);
					break;
			}

			// deal with results
			$this->assign('requests', $results);
			$this->assign('term', $searchTerm);
			$this->assign('target', $searchType);

			$userIds = array_map(
				function(Request $entry) {
					return $entry->getReserved();
				},
				$results);
			$userList = User::getUsernames($userIds, $this->getDatabase());
			$this->assign('userlist', $userList);

			$this->assignCSRFToken();
			$this->setTemplate('search/searchResult.tpl');
		}
		else {
			$this->assignCSRFToken();
			$this->setTemplate('search/searchForm.tpl');
		}
	}

	/**
	 * Gets search results by name
	 *
	 * @param string $searchTerm
	 *
	 * @returns Request[]
	 */
	private function getNameSearchResults($searchTerm)
	{
		$padded = '%' . $searchTerm . '%';

		return RequestSearchHelper::get($this->getDatabase())
			->byName($padded)
			->excludingPurgedData($this->getSiteConfiguration())
			->fetch();
	}

	/**
	 * Gets search results by email
	 *
	 * @param string $searchTerm
	 *
	 * @return Request[]
	 * @throws ApplicationLogicException
	 */
	private function getEmailSearchResults($searchTerm)
	{
		if ($searchTerm === "@") {
			throw new ApplicationLogicException('The search term "@" is not valid for email address searches!');
		}

		$padded = '%' . $searchTerm . '%';

		return RequestSearchHelper::get($this->getDatabase())
			->byEmailAddress($padded)
			->excludingPurgedData($this->getSiteConfiguration())
			->fetch();
	}

	/**
	 * Gets search results by IP address or XFF IP address
	 *
	 * @param string $searchTerm
	 *
	 * @returns Request[]
	 */
	private function getIpSearchResults($searchTerm)
	{
		return RequestSearchHelper::get($this->getDatabase())
			->byIp($searchTerm)
			->excludingPurgedData($this->getSiteConfiguration())
			->fetch();
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
		return $this->getSecurityManager()->configure()->asInternalPage();
	}

	/**
	 * @param string $searchType
	 * @param string $searchTerm
	 *
	 * @param string $errorMessage
	 *
	 * @return bool true if parameters are valid
	 * @throws ApplicationLogicException
	 */
	protected function validateSearchParameters($searchType, $searchTerm, &$errorMessage)
	{
		if (!in_array($searchType, array('name', 'email', 'ip'))) {
			$errorMessage = 'Unknown search type';
			return false;
		}

		if ($searchTerm === '%' || $searchTerm === '' || $searchTerm === null) {
			$errorMessage = 'No search term specified entered';
			return false;
		}

		$errorMessage = "";
		return true;
	}
}
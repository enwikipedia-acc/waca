<?php

namespace Waca\Pages;

use Exception;
use PDO;
use Request;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageSearch extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		// Dual-mode page
		if (WebRequest::wasPosted()) {
			// TODO: logging

			$searchType = WebRequest::postString('type');
			$searchTerm = WebRequest::postString('term');

			if (in_array($searchType, array('name', 'email', 'ip'))) {
				try {
					if ($searchTerm === '%' || $searchTerm === '') {
						throw new ApplicationLogicException('No search term specified entered');
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

					$this->setTemplate('search/searchResult.tpl');
				}
				catch (ApplicationLogicException $ex) {
					// error occurred retrieving results
					// todo: handle more gracefully.
					throw new Exception('An error occurred with the search request.', 0, $ex);
				}
			}
			else {
				// todo: handle more gracefully.
				throw new Exception('Unknown search type');
			}
		}
		else {
			$this->setTemplate('search/searchForm.tpl');
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
		return SecurityConfiguration::internalPage();
	}

	/**
	 * Gets search results by name
	 * @param $searchTerm string
	 * @returns array<Request>
	 */
	private function getNameSearchResults($searchTerm)
	{
		$padded = '%' . $searchTerm . '%';

		$database = gGetDb();

		$query = 'SELECT * FROM request WHERE name LIKE :term AND email <> :clearedEmail AND ip <> :clearedIp';
		$statement = $database->prepare($query);
		$statement->bindValue(":term", $padded);
		$statement->bindValue(":clearedEmail", $this->getSiteConfiguration()->getDataClearEmail());
		$statement->bindValue(":clearedIp", $this->getSiteConfiguration()->getDataClearIp());
		$statement->execute();

		/** @var Request $r */
		$requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
		foreach ($requests as $r) {
			$r->setDatabase($database);
			$r->isNew = false;
		}

		return $requests;
	}

	/**
	 * Gets search results by email
	 * @param $searchTerm string
	 * @return array <Request>
	 * @throws ApplicationLogicException
	 */
	private function getEmailSearchResults($searchTerm)
	{
		if ($searchTerm === "@") {
			throw new ApplicationLogicException('The search term "@" is not valid for email address searches!');
		}

		$padded = '%' . $searchTerm . '%';

		$database = gGetDb();

		$query = 'SELECT * FROM request WHERE email LIKE :term AND email <> :clearedEmail AND ip <> :clearedIp';
		$statement = $database->prepare($query);
		$statement->bindValue(":term", $padded);
		$statement->bindValue(":clearedEmail", $this->getSiteConfiguration()->getDataClearEmail());
		$statement->bindValue(":clearedIp", $this->getSiteConfiguration()->getDataClearIp());
		$statement->execute();

		/** @var Request $r */
		$requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
		foreach ($requests as $r) {
			$r->setDatabase($database);
			$r->isNew = false;
		}

		return $requests;
	}

	/**
	 * Gets search results by IP address or XFF IP address
	 * @param $searchTerm string
	 * @returns array<Request>
	 */
	private function getIpSearchResults($searchTerm)
	{
		$padded = '%' . $searchTerm . '%';

		$database = gGetDb();

		$query = <<<SQL
SELECT * FROM request
WHERE ip LIKE :term OR forwardedip LIKE :paddedTerm AND email <> :clearedEmail AND ip <> :clearedIp
SQL;

		$statement = $database->prepare($query);
		$statement->bindValue(":term", $searchTerm);
		$statement->bindValue(":paddedTerm", $padded);
		$statement->bindValue(":clearedEmail", $this->getSiteConfiguration()->getDataClearEmail());
		$statement->bindValue(":clearedIp", $this->getSiteConfiguration()->getDataClearIp());
		$statement->execute();

		/** @var Request $r */
		$requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
		foreach ($requests as $r) {
			$r->setDatabase($database);
			$r->isNew = false;
		}

		return $requests;
	}
}
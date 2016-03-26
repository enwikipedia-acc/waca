<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use PDO;
use Waca\DataObjects\Request;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

class RequestSearchHelper
{
	/** @var PdoDatabase */
	private $database;
	/** @var string */
	private $query;
	/** @var array */
	private $parameterList = array();

	/**
	 * RequestSearchHelper constructor.
	 *
	 * @param PdoDatabase $database
	 */
	private function __construct(PdoDatabase $database)
	{
		$this->database = $database;

		// initialise query
		// (the 1=1 condition will be optimised out of the query by the query planner, and simplifies our code here)
		// Note that we use positional parameters instead of named parameters because we don't know many times different
		// options will be called (looking at excluding() here, but there's the option for others).
		$this->query = <<<SQL
SELECT * /* RequestSearchHelper */
FROM request WHERE 1 = 1
SQL;
	}

	/**
	 * Initiates a search for requests
	 *
	 * @param PdoDatabase $database
	 *
	 * @return RequestSearchHelper
	 */
	public static function get(PdoDatabase $database)
	{
		$helper = new RequestSearchHelper($database);

		return $helper;
	}

	/**
	 * Returns the requested requests
	 *
	 * @return Request[]
	 */
	public function fetch()
	{
		$statement = $this->database->prepare($this->query);
		$statement->execute($this->parameterList);

		/** @var Request[] $returnedObjects */
		$returnedObjects = $statement->fetchAll(PDO::FETCH_CLASS, Request::class);
		foreach ($returnedObjects as $req) {
			$req->setDatabase($this->database);
		}

		return $returnedObjects;
	}

	/**
	 * Filters the results by IP address
	 *
	 * @param string $ipAddress
	 *
	 * @return $this
	 */
	public function byIp($ipAddress)
	{
		$this->query .= ' AND (ip LIKE ? OR forwardedip LIKE ?)';
		$this->parameterList[] = $ipAddress;
		$this->parameterList[] = '%' . trim($ipAddress, '%') . '%';

		return $this;
	}

	/**
	 * Filters the results by email address
	 *
	 * @param string $emailAddress
	 *
	 * @return $this
	 */
	public function byEmailAddress($emailAddress)
	{
		$this->query .= ' AND email LIKE ?';
		$this->parameterList[] = $emailAddress;

		return $this;
	}

	/**
	 * Filters the results by name
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function byName($name)
	{
		$this->query .= ' AND name LIKE ?';
		$this->parameterList[] = $name;

		return $this;
	}

	/**
	 * Excludes a request from the results
	 *
	 * @param int $requestId
	 *
	 * @return $this
	 */
	public function excludingRequest($requestId)
	{
		$this->query .= ' AND id <> ?';
		$this->parameterList[] = $requestId;

		return $this;
	}

	/**
	 * Filters the results to only those with a confirmed email address
	 *
	 * @return $this
	 */
	public function withConfirmedEmail()
	{
		$this->query .= ' AND emailconfirm = ?';
		$this->parameterList[] = 'Confirmed';

		return $this;
	}

	/**
	 * Filters the results to exclude purged data
	 *
	 * @param SiteConfiguration $configuration
	 *
	 * @return $this
	 */
	public function excludingPurgedData(SiteConfiguration $configuration)
	{
		$this->query .= ' AND ip <> ? AND email <> ?';
		$this->parameterList[] = $configuration->getDataClearIp();
		$this->parameterList[] = $configuration->getDataClearEmail();

		return $this;
	}
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\SearchHelpers;

use PDO;
use Waca\DataObject;
use Waca\PdoDatabase;

abstract class SearchHelperBase
{
	/** @var PdoDatabase */
	protected $database;
	/** @var array */
	protected $parameterList = array();
	/** @var null|int */
	private $limit = null;
	/** @var null|int */
	private $offset = null;
	private $orderBy = null;
	/**
	 * @var string The where clause.
	 *
	 * (the 1=1 condition will be optimised out of the query by the query planner, and simplifies our code here). Note
	 * that we use positional parameters instead of named parameters because we don't know many times different options
	 * will be called (looking at excluding() here, but there's the option for others).
	 */
	protected $whereClause = 'WHERE 1 = 1';
	/** @var string */
	private $table;

	/**
	 * SearchHelperBase constructor.
	 *
	 * @param PdoDatabase $database
	 * @param string      $table
	 * @param null|string $order Order by clause, excluding ORDER BY.
	 */
	protected function __construct(PdoDatabase $database, $table, $order = null)
	{
		$this->database = $database;
		$this->table = $table;
		$this->orderBy = $order;
	}

	/**
	 * @param $targetClass
	 *
	 * @return DataObject[]
	 */
	protected function fetchObjects($targetClass)
	{
		$query = 'SELECT * FROM ' . $this->table . ' ' . $this->whereClause;
		$query .= $this->applyOrder();
		$query .= $this->applyLimit();

		$statement = $this->database->prepare($query);
		$statement->execute($this->parameterList);

		/** @var DataObject[] $returnedObjects */
		$returnedObjects = $statement->fetchAll(PDO::FETCH_CLASS, $targetClass);
		foreach ($returnedObjects as $req) {
			$req->setDatabase($this->database);
		}

		return $returnedObjects;
	}

	/**
	 * @param int $count Returns the record count of the result set
	 *
	 * @return $this
	 */
	public function getRecordCount(&$count)
	{
		$query = 'SELECT COUNT(1) FROM ' . $this->table . ' ' . $this->whereClause;
		$statement = $this->database->prepare($query);
		$statement->execute($this->parameterList);

		$count = $statement->fetchColumn(0);
		$statement->closeCursor();

		return $this;
	}

	/**
	 * Limits the results
	 *
	 * @param integer      $limit
	 * @param integer|null $offset
	 *
	 * @return $this
	 *
	 */
	public function limit($limit, $offset = null)
	{
		$this->limit = $limit;
		$this->offset = $offset;

		return $this;
	}

	private function applyLimit()
	{
		$clause = '';
		if ($this->limit !== null) {
			$clause = ' LIMIT ?';
			$this->parameterList[] = $this->limit;

			if ($this->offset !== null) {
				$clause .= ' OFFSET ?';
				$this->parameterList[] = $this->offset;
			}
		}

		return $clause;
	}

	private function applyOrder()
	{
		if ($this->orderBy !== null) {
			return ' ORDER BY ' . $this->orderBy;
		}

		return '';
	}
}
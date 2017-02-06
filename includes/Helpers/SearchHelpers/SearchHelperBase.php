<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\SearchHelpers;

use PDO;
use PDOStatement;
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
    protected $whereClause = ' WHERE 1 = 1';
    /** @var string */
    protected $table;
    protected $joinClause = '';
    private $targetClass;

    /**
     * SearchHelperBase constructor.
     *
     * @param PdoDatabase $database
     * @param string      $table
     * @param             $targetClass
     * @param null|string $order Order by clause, excluding ORDER BY.
     */
    protected function __construct(PdoDatabase $database, $table, $targetClass, $order = null)
    {
        $this->database = $database;
        $this->table = $table;
        $this->orderBy = $order;
        $this->targetClass = $targetClass;
    }

    /**
     * Finalises the database query, and executes it, returning a set of objects.
     *
     * @return DataObject[]
     */
    public function fetch()
    {
        $statement = $this->getData();

        /** @var DataObject[] $returnedObjects */
        $returnedObjects = $statement->fetchAll(PDO::FETCH_CLASS, $this->targetClass);
        foreach ($returnedObjects as $req) {
            $req->setDatabase($this->database);
        }

        return $returnedObjects;
    }

    /**
     * Finalises the database query, and executes it, returning only the requested column.
     *
     * @param string $column The required column
     * @return array
     */
    public function fetchColumn($column){
        $statement = $this->getData(array($column));

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function fetchMap($column){
        $statement = $this->getData(array('id', $column));

        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $map = array();

        foreach ($data as $row) {
            $map[$row['id']] = $row[$column];
        }

        return $map;
    }

    /**
     * @param int $count Returns the record count of the result set
     *
     * @return $this
     */
    public function getRecordCount(&$count)
    {
        $query = 'SELECT /* SearchHelper */ COUNT(*) FROM ' . $this->table . ' origin ';
        $query .= $this->joinClause . $this->whereClause;

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

    /**
     * @param array $columns
     *
     * @return PDOStatement
     */
    private function getData($columns = array('*'))
    {
        $query = $this->buildQuery($columns);
        $query .= $this->applyOrder();
        $query .= $this->applyLimit();

        $statement = $this->database->prepare($query);
        $statement->execute($this->parameterList);

        return $statement;
    }

    /**
     * @param array $columns
     *
     * @return string
     */
    protected function buildQuery($columns)
    {
        $colData = array();
        foreach ($columns as $c) {
            $colData[] = 'origin.' . $c;
        }

        $query = 'SELECT /* SearchHelper */ ' . implode(', ', $colData) . ' FROM ' . $this->table . ' origin ';
        $query .= $this->joinClause . $this->whereClause;

        return $query;
    }

    public function inIds($idList) {
        $this->inClause('id', $idList);
        return $this;
    }

    protected function inClause($column, $values) {
        if (count($values) === 0) {
            return;
        }

        // Urgh. OK. You can't use IN() with parameters directly, so let's munge something together.
        $valueCount = count($values);

        // Firstly, let's create a string of question marks, which will do as positional parameters.
        $inSection = str_repeat('?,', $valueCount - 1) . '?';

        $this->whereClause .= " AND {$column} IN ({$inSection})";
        $this->parameterList = array_merge($this->parameterList, $values);
    }
}

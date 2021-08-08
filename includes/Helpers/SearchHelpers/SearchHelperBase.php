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
use Waca\Exceptions\ApplicationLogicException;
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
    protected $orderBy;
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
    protected $groupByClause = '';
    protected $modifiersClause = '';
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
     * @param string $whereClauseSection
     * @param array  $values
     *
     * @return array
     */
    protected function fetchByParameter($whereClauseSection, $values)
    {
        $this->whereClause .= $whereClauseSection;

        $countQuery = 'SELECT /* SearchHelper */ COUNT(*) FROM ' . $this->table . ' origin ';
        $countQuery .= $this->joinClause . $this->whereClause;

        $query = $this->buildQuery(array('*'));
        $query .= $this->applyOrder();

        // shuffle around parameters
        // applyLimit() appends parameters to the parameter list, which is useless when we want to run
        // many queries with different parameters. As such, we back up the parameter list, wipe it, apply the limit
        // parameters, and hold them separately, merging again prior to running the actual query.
        $localParameterList = $this->parameterList;
        $this->parameterList = array();
        $query .= $this->applyLimit();
        $limitParameters = $this->parameterList;

        $statement = $this->database->prepare($query);
        $countStatement = $this->database->prepare($countQuery);

        $result = array();
        foreach ($values as $v) {
            // reset parameter list
            $params = $localParameterList;
            $params[] = $v;

            $countStatement->execute($params);

            // reapply the limit parameters
            $params = array_merge($params, $limitParameters);

            $statement->execute($params);

            /** @var DataObject[] $returnedObjects */
            $returnedObjects = $statement->fetchAll(PDO::FETCH_CLASS, $this->targetClass);
            foreach ($returnedObjects as $req) {
                $req->setDatabase($this->database);
            }

            $result[$v] = array(
                'count' => $countStatement->fetchColumn(0),
                'data'  => $returnedObjects,
            );
        }

        return $result;
    }

    /**
     * Finalises the database query, and executes it, returning only the requested column.
     *
     * @param string $column The required column
     *
     * @param bool   $distinct
     *
     * @return array
     * @throws ApplicationLogicException
     */
    public function fetchColumn($column, $distinct = false)
    {
        if ($distinct) {
            if ($this->groupByClause !== '') {
                throw new ApplicationLogicException('Cannot apply distinct to column fetch already using group by');
            }

            $this->groupByClause = ' GROUP BY origin.' . $column;
        }

        $statement = $this->getData(array($column));

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function fetchMap($column)
    {
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

        /** @var PDOStatement $statement */
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

        $query = "SELECT {$this->modifiersClause} /* SearchHelper */ " . implode(', ', $colData) . ' FROM ' . $this->table . ' origin ';
        $query .= $this->joinClause . $this->whereClause . $this->groupByClause;

        return $query;
    }

    public function inIds($idList)
    {
        $this->inClause('id', $idList);

        return $this;
    }

    protected function inClause($column, $values)
    {
        if (count($values) === 0) {
            return;
        }

        // You can't use IN() with parameters directly, so let's munge something together.
        // Let's create a string of question marks, which will do as positional parameters.
        $valueCount = count($values);
        $inSection = str_repeat('?,', $valueCount - 1) . '?';

        $this->whereClause .= " AND {$column} IN ({$inSection})";
        $this->parameterList = array_merge($this->parameterList, $values);
    }
}

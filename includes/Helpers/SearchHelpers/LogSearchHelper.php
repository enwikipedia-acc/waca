<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers\SearchHelpers;

use Waca\DataObjects\Log;
use Waca\PdoDatabase;

class LogSearchHelper extends SearchHelperBase
{
    /**
     * LogSearchHelper constructor.
     *
     * @param PdoDatabase $database
     */
    protected function __construct(PdoDatabase $database)
    {
        parent::__construct($database, 'log', Log::class, 'timestamp DESC');
    }

    /**
     * Initiates a search for logs
     *
     * @param PdoDatabase $database
     * @param int|null $domain The domain to search for. This will retrieve both logs for the specified domain and
     *                         global logs if a value is specified. If null, only global logs are returned.
     *
     * @return LogSearchHelper
     */
    public static function get(PdoDatabase $database, ?int $domain)
    {
        $helper = new LogSearchHelper($database);

        if ($domain === null) {
            $helper->whereClause .= ' AND domain IS NULL';
        }
        else {
            $helper->whereClause .= ' AND coalesce(domain, ?) = ?';
            $helper->parameterList[] = $domain;
            $helper->parameterList[] = $domain;
        }

        return $helper;
    }

    /**
     * Filters the results by user
     *
     * @param int $userId
     *
     * @return $this
     */
    public function byUser($userId)
    {
        $this->whereClause .= ' AND user = ?';
        $this->parameterList[] = $userId;

        return $this;
    }

    /**
     * Filters the results by log action
     *
     * @param string $action
     *
     * @return $this
     */
    public function byAction($action)
    {
        $this->whereClause .= ' AND action = ?';
        $this->parameterList[] = $action;

        return $this;
    }

    /**
     * Filters the results by object type
     *
     * @param string $objectType
     *
     * @return $this
     */
    public function byObjectType($objectType)
    {
        $this->whereClause .= ' AND objecttype = ?';
        $this->parameterList[] = $objectType;

        return $this;
    }

    /**
     * Filters the results by object type
     *
     * @param integer $objectId
     *
     * @return $this
     */
    public function byObjectId($objectId)
    {
        $this->whereClause .= ' AND objectid = ?';
        $this->parameterList[] = $objectId;

        return $this;
    }
}
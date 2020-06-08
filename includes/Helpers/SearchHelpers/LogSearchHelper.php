<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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
     * Initiates a search for requests
     *
     * @param PdoDatabase $database
     *
     * @return LogSearchHelper
     */
    public static function get(PdoDatabase $database)
    {
        $helper = new LogSearchHelper($database);

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
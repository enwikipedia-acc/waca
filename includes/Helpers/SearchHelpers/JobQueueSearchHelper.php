<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers\SearchHelpers;

use Waca\DataObjects\JobQueue;
use Waca\PdoDatabase;

class JobQueueSearchHelper extends SearchHelperBase
{
    protected function __construct(PdoDatabase $database)
    {
        parent::__construct($database, 'jobqueue', JobQueue::class, null);
    }

    /**
     * @param PdoDatabase $database
     * @param int         $domain
     *
     * @return JobQueueSearchHelper
     */
    public static function get(PdoDatabase $database, int $domain)
    {
        $helper = new JobQueueSearchHelper($database);

        $helper->whereClause .= ' AND domain = ?';
        $helper->parameterList[] = $domain;

        return $helper;
    }

    /**
     * @param string[] $statuses
     *
     * @return $this
     */
    public function statusIn($statuses)
    {
        $this->inClause('status', $statuses);

        return $this;
    }

    /**
     * @return $this
     */
    public function notAcknowledged()
    {
        $this->whereClause .= ' AND (acknowledged IS NULL OR acknowledged = 0)';

        return $this;
    }

    public function byTask($task)
    {
        $this->whereClause .= ' AND task = ?';
        $this->parameterList[] = $task;

        return $this;
    }

    public function byUser($userId)
    {
        $this->whereClause .= ' AND user = ?';
        $this->parameterList[] = $userId;

        return $this;
    }

    public function byStatus($status)
    {
        $this->whereClause .= ' AND status = ?';
        $this->parameterList[] = $status;

        return $this;
    }

    public function byRequest(int $request) : JobQueueSearchHelper
    {
        $this->whereClause .= ' AND request = ?';
        $this->parameterList[] = $request;

        return $this;
    }
    
    public function newestFirst()
    {
        $this->orderBy = 'id DESC';
        
        return $this;
    }     
}

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use Exception;
use Waca\Background\Task\BotCreationTask;
use Waca\Background\Task\UserCreationTask;
use Waca\Background\Task\WelcomeUserTask;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;

class JobQueue extends DataObject
{
    /*
     * Status workflow is this:
     *
     * 1) Queued. The job has been added to the queue.
     * 2) Ready. The job is ready to be run in the next queue run.
     * 3) Waiting. The job has been picked up by the worker
     * 4) Running. The job is actively being processed.
     * 5) Complete / Failed. The job has been processed
     *
     * A job can move to Cancelled at any point, and will be cancelled automatically.
     *
     * 'held' is not used by the system, and is intended for manual pauses.
     *
     */

    const STATUS_QUEUED = 'queued';
    const STATUS_READY = 'ready';
    const STATUS_WAITING = 'waiting';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_HELD = 'held';

    /** @var string */
    private $task;
    /** @var int */
    private $user;
    /** @var int */
    private $request;
    /** @var int */
    private $emailtemplate;
    /** @var string */
    private $status;
    /** @var string */
    private $enqueue;
    /** @var string */
    private $parameters;
    /** @var string */
    private $error;
    /** @var int */
    private $acknowledged;
    /** @var int */
    private $parent;

    /**
     * This feels like the least bad place to put this method.
     */
    public static function getTaskDescriptions()
    {
        return array(
            BotCreationTask::class  => 'Create account (via bot)',
            UserCreationTask::class => 'Create account (via OAuth)',
            WelcomeUserTask::class  => 'Welcome user',
        );
    }

    /**
     * Saves a data object to the database, either updating or inserting a record.
     * @return void
     * @throws Exception
     * @throws OptimisticLockFailedException
     */
    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO jobqueue (task, user, request, emailtemplate, parameters, parent, status) 
                VALUES (:task, :user, :request, :emailtemplate, :parameters, :parent, 'queued')
SQL
            );
            $statement->bindValue(":task", $this->task);
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":request", $this->request);
            $statement->bindValue(":emailtemplate", $this->emailtemplate);
            $statement->bindValue(":parameters", $this->parameters);
            $statement->bindValue(":parent", $this->parent);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            // update
            $statement = $this->dbObject->prepare(<<<SQL
                UPDATE jobqueue SET 
                      status = :status
                    , error = :error
                    , acknowledged = :ack
                    , updateversion = updateversion + 1
                WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(":id", $this->id);
            $statement->bindValue(":updateversion", $this->updateversion);

            $statement->bindValue(":status", $this->status);
            $statement->bindValue(":error", $this->error);
            $statement->bindValue(":ack", $this->acknowledged);

            if (!$statement->execute()) {
                throw new Exception($statement->errorInfo());
            }

            if ($statement->rowCount() !== 1) {
                throw new OptimisticLockFailedException();
            }

            $this->updateversion++;
        }
    }

    #region Properties

    /**
     * @return string
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param string $task
     */
    public function setTask($task)
    {
        $this->task = $task;
    }

    /**
     * @return int
     */
    public function getTriggerUserId()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setTriggerUserId($user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param int $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getEnqueue()
    {
        return $this->enqueue;
    }

    /**
     * @param string $enqueue
     */
    public function setEnqueue($enqueue)
    {
        $this->enqueue = $enqueue;
    }

    /**
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return int
     */
    public function getAcknowledged()
    {
        return $this->acknowledged;
    }

    /**
     * @param int $acknowledged
     */
    public function setAcknowledged($acknowledged)
    {
        $this->acknowledged = $acknowledged;
    }

    /**
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param int $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return int
     */
    public function getEmailTemplate()
    {
        return $this->emailtemplate;
    }

    /**
     * @param int $emailTemplate
     */
    public function setEmailTemplate($emailTemplate)
    {
        $this->emailtemplate = $emailTemplate;
    }
    #endregion
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Exception;
use PDO;
use Waca\Background\BackgroundTaskBase;
use Waca\Background\Task\BotCreationTask;
use Waca\Background\Task\UserCreationTask;
use Waca\Background\Task\WelcomeUserTask;
use Waca\DataObjects\JobQueue;
use Waca\ExceptionHandler;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\Tasks\ConsoleTaskBase;

class RunJobQueueTask extends ConsoleTaskBase
{
    private $taskList = array(
        WelcomeUserTask::class,
        BotCreationTask::class,
        UserCreationTask::class
    );

    public function execute()
    {
        $database = $this->getDatabase();

        // ensure we're running inside a tx here.
        if (!$database->hasActiveTransaction()) {
            $database->beginTransaction();
        }

        $sql = 'SELECT * FROM jobqueue WHERE status = :status ORDER BY enqueue LIMIT :lim';
        $statement = $database->prepare($sql);
        $statement->execute(array(
            ':status' => JobQueue::STATUS_READY,
            ':lim' => $this->getSiteConfiguration()->getJobQueueBatchSize()
        ));

        /** @var JobQueue[] $queuedJobs */
        $queuedJobs = $statement->fetchAll(PDO::FETCH_CLASS, JobQueue::class);

        // mark all the jobs as running, and commit the txn so we're not holding onto long-running transactions.
        // We'll re-lock the row when we get to it.
        foreach ($queuedJobs as $job) {
            $job->setDatabase($database);
            $job->setStatus(JobQueue::STATUS_WAITING);
            $job->setError(null);
            $job->setAcknowledged(null);
            $job->save();
        }

        $database->commit();

        set_error_handler(array(RunJobQueueTask::class, 'errorHandler'), E_ALL);

        foreach ($queuedJobs as $job) {
            try {
                // refresh from the database
                /** @var JobQueue $job */
                $job = JobQueue::getById($job->getId(), $database);

                if ($job->getStatus() !== JobQueue::STATUS_WAITING) {
                    continue;
                }

                $database->beginTransaction();
                $job->setStatus(JobQueue::STATUS_RUNNING);
                $job->save();
                $database->commit();

                $database->beginTransaction();

                // re-lock the job
                $job->setStatus(JobQueue::STATUS_RUNNING);
                $job->save();

                // validate we're allowed to run the requested task (whitelist)
                if (!in_array($job->getTask(), $this->taskList)) {
                    throw new ApplicationLogicException('Job task not registered');
                }

                // Create a task.
                $taskName = $job->getTask();

                if (!class_exists($taskName)) {
                    throw new ApplicationLogicException('Job task does not exist');
                }

                /** @var BackgroundTaskBase $task */
                $task = new $taskName;

                $this->setupTask($task, $job);
                $task->run();
            }
            catch (Exception $ex) {
                $database->rollBack();
                $database->beginTransaction();

                /** @var JobQueue $job */
                $job = JobQueue::getById($job->getId(), $database);
                $job->setDatabase($database);
                $job->setStatus(JobQueue::STATUS_FAILED);
                $job->setError($ex->getMessage());
                $job->setAcknowledged(0);
                $job->save();

                Logger::backgroundJobIssue($this->getDatabase(), $job);

                $database->commit();
            }
            finally {
                $database->commit();
            }
        }

        $this->stageQueuedTasks($database);
    }

    /**
     * @param BackgroundTaskBase $task
     * @param JobQueue           $job
     */
    private function setupTask(BackgroundTaskBase $task, JobQueue $job)
    {
        $task->setJob($job);
        $task->setDatabase($this->getDatabase());
        $task->setHttpHelper($this->getHttpHelper());
        $task->setOauthProtocolHelper($this->getOAuthProtocolHelper());
        $task->setEmailHelper($this->getEmailHelper());
        $task->setSiteConfiguration($this->getSiteConfiguration());
        $task->setNotificationHelper($this->getNotificationHelper());
    }

    /** @noinspection PhpUnusedParameterInspection */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new Exception($errfile . "@" . $errline . ": " . $errstr);
    }

    /**
     * Stages tasks for execution during the *next* jobqueue run.
     *
     * This is to build in some delay between enqueue and execution to allow for accidentally-triggered tasks to be
     * cancelled.
     *
     * @param PdoDatabase $database
     */
    protected function stageQueuedTasks(PdoDatabase $database): void
    {
        try {
            $database->beginTransaction();

            $sql = 'SELECT * FROM jobqueue WHERE status = :status ORDER BY enqueue LIMIT :lim';
            $statement = $database->prepare($sql);

            // use a larger batch size than the main runner, but still keep it limited in case things go crazy.
            $statement->execute(array(
                ':status' => JobQueue::STATUS_QUEUED,
                ':lim' => $this->getSiteConfiguration()->getJobQueueBatchSize() * 2
            ));

            /** @var JobQueue[] $queuedJobs */
            $queuedJobs = $statement->fetchAll(PDO::FETCH_CLASS, JobQueue::class);

            foreach ($queuedJobs as $job) {
                $job->setDatabase($database);
                $job->setStatus(JobQueue::STATUS_READY);
                $job->save();
            }

            $database->commit();
        }
        catch (Exception $ex) {
            $database->rollBack();
            ExceptionHandler::logExceptionToDisk($ex, $this->getSiteConfiguration());
        }
    }
}

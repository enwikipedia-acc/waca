<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Waca\Background\Task\WelcomeUserTask;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PdoDatabase;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

abstract class RequestActionBase extends InternalPageBase
{
    /**
     * @param PdoDatabase $database
     *
     * @return Request
     * @throws ApplicationLogicException
     */
    protected function getRequest(PdoDatabase $database)
    {
        $requestId = WebRequest::postInt('request');
        if ($requestId === null) {
            throw new ApplicationLogicException('Request ID not found');
        }

        /** @var Request $request */
        $request = Request::getById($requestId, $database);

        if ($request === false) {
            throw new ApplicationLogicException('Request not found');
        }

        return $request;
    }

    final protected function checkPosted()
    {
        // if the request was not posted, send the user away.
        if (!WebRequest::wasPosted()) {
            throw new ApplicationLogicException('This page does not support GET methods.');
        }

        // validate the CSRF token
        $this->validateCSRFToken();
    }

    /**
     * @param Request     $request
     * @param             $parentTaskId
     * @param User        $user
     * @param PdoDatabase $database
     */
    protected function enqueueWelcomeTask(Request $request, $parentTaskId, User $user, PdoDatabase $database)
    {
        $welcomeTask = new JobQueue();
        $welcomeTask->setDomain(1); // FIXME: domains!
        $welcomeTask->setTask(WelcomeUserTask::class);
        $welcomeTask->setRequest($request->getId());
        $welcomeTask->setParent($parentTaskId);
        $welcomeTask->setTriggerUserId($user->getId());
        $welcomeTask->setDatabase($database);
        $welcomeTask->save();
    }
}
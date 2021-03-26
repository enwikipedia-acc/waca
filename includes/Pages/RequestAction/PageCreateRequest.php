<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Waca\Background\Task\BotCreationTask;
use Waca\Background\Task\UserCreationTask;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\RequestStatus;
use Waca\Security\SecurityManager;
use Waca\SessionAlert;
use Waca\WebRequest;

/**
 * Class PageCreateRequest
 *
 * This class wraps the auto-creation closes, enqueuing items onto the JobQueue for processing. This is different from
 * PageCloseRequest, which encapsulates all of the non-JobQueue closes.
 *
 * @package Waca\Pages\RequestAction
 */
class PageCreateRequest extends RequestActionBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     * @throws AccessDeniedException
     * @throws ApplicationLogicException
     */
    protected function main()
    {
        $this->checkPosted();

        $database = $this->getDatabase();

        $request = $this->getRequest($database);
        $template = $this->getTemplate($database);
        $creationMode = $this->getCreationMode();
        $user = User::getCurrent($database);

        $secMgr = $this->getSecurityManager();
        if ($secMgr->allows('RequestCreation', User::CREATION_BOT, $user) !== SecurityManager::ALLOWED
            && $creationMode === 'bot'
        ) {
            throw new AccessDeniedException($secMgr);
        }
        elseif ($secMgr->allows('RequestCreation', User::CREATION_OAUTH, $user) !== SecurityManager::ALLOWED
            && $creationMode === 'oauth'
        ) {
            throw new AccessDeniedException($secMgr);
        }

        if ($request->getEmailSent()) {
            throw new ApplicationLogicException('This requester has already had an email sent to them. Please fall back to manual creation or a custom close');
        }

        $request->setStatus(RequestStatus::JOBQUEUE);
        $request->setReserved(null);
        $request->save();

        Logger::enqueuedJobQueue($database, $request);

        $creationTaskId = $this->enqueueCreationTask($creationMode, $request, $template, $user, $database);

        if ($user->getWelcomeTemplate() !== null && !WebRequest::postBoolean('skipAutoWelcome')) {
            $this->enqueueWelcomeTask($request, $creationTaskId, $user, $database);
        }

        $this->getNotificationHelper()->requestCloseQueued($request, $template->getName());

        SessionAlert::success("Request {$request->getId()} has been queued for autocreation");

        $this->redirect();
    }

    protected function getCreationMode()
    {
        $creationMode = WebRequest::postString('mode');
        if ($creationMode !== 'oauth' && $creationMode !== 'bot') {
            throw new ApplicationLogicException('Unknown creation mode');
        }

        return $creationMode;
    }

    /**
     * @param PdoDatabase $database
     *
     * @return EmailTemplate
     * @throws ApplicationLogicException
     */
    protected function getTemplate(PdoDatabase $database)
    {
        $templateId = WebRequest::postInt('template');
        if ($templateId === null) {
            throw new ApplicationLogicException('No template specified');
        }

        /** @var EmailTemplate $template */
        $template = EmailTemplate::getById($templateId, $database);
        if ($template === false || !$template->getActive()) {
            throw new ApplicationLogicException('Invalid or inactive template specified');
        }

        if ($template->getDefaultAction() !== EmailTemplate::CREATED) {
            throw new ApplicationLogicException('Specified template is not a creation template!');
        }

        return $template;
    }

    /**
     * @param PdoDatabase $database
     *
     * @return Request
     * @throws ApplicationLogicException
     */
    protected function getRequest(PdoDatabase $database)
    {
        $request = parent::getRequest($database);

        if ($request->getStatus() == RequestStatus::CLOSED) {
            throw new ApplicationLogicException('Request is already closed');
        }

        return $request;
    }

    /**
     * @param               $creationMode
     * @param Request       $request
     * @param EmailTemplate $template
     * @param User          $user
     *
     * @param PdoDatabase   $database
     *
     * @return int
     * @throws ApplicationLogicException
     */
    protected function enqueueCreationTask(
        $creationMode,
        Request $request,
        EmailTemplate $template,
        User $user,
        PdoDatabase $database
    ) {
        $creationTaskClass = null;

        if ($creationMode == "oauth") {
            $creationTaskClass = UserCreationTask::class;
        }

        if ($creationMode == "bot") {
            $creationTaskClass = BotCreationTask::class;
        }

        if ($creationTaskClass === null) {
            throw new ApplicationLogicException('Cannot determine creation mode');
        }

        $creationTask = new JobQueue();
        $creationTask->setTask($creationTaskClass);
        $creationTask->setRequest($request->getId());
        $creationTask->setEmailTemplate($template->getId());
        $creationTask->setTriggerUserId($user->getId());
        $creationTask->setDatabase($database);
        $creationTask->save();

        $creationTaskId = $creationTask->getId();

        return $creationTaskId;
    }
}

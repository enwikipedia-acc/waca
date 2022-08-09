<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Request;

use Exception;
use Waca\DataObjects\Comment;
use Waca\DataObjects\Domain;
use Waca\DataObjects\Request;
use Waca\DataObjects\RequestForm;
use Waca\DataObjects\RequestQueue;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Helpers\BanHelper;
use Waca\Helpers\MarkdownRenderingHelper;
use Waca\SessionAlert;
use Waca\Tasks\PublicInterfacePageBase;
use Waca\Validation\RequestValidationHelper;
use Waca\Validation\ValidationError;
use Waca\WebRequest;

class PageRequestAccount extends PublicInterfacePageBase
{
    /** @var RequestValidationHelper do not use directly. */
    private $validationHelper;

    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     * @throws OptimisticLockFailedException
     * @throws Exception
     */
    protected function main()
    {
        // dual mode page
        if (WebRequest::wasPosted()) {
            $request = $this->createNewRequest(null);
            $this->handleFormPost($request);
        }
        else {
            $this->handleFormRefilling();

            $this->setTemplate('request/request-form.tpl');
        }
    }

    /**
     * Handles dynamic request forms.
     * @return void
     * @throws OptimisticLockFailedException
     * @throws Exception
     */
    protected function dynamic()
    {
        $database = $this->getDatabase();

        $pathInfo = WebRequest::pathInfo();
        $domain = Domain::getByShortName($pathInfo[1], $database);
        if ($domain === false || !$domain->isEnabled()) {
            throw new ApplicationLogicException("This form is not available at this time.");
        }

        $form = RequestForm::getByPublicEndpoint($database, $pathInfo[2], $domain->getId());

        if ($form === false || !$form->isEnabled()) {
            throw new ApplicationLogicException("This form is not available at this time.");
        }

        // dual mode page
        if (WebRequest::wasPosted()) {
            $request = $this->createNewRequest($form);
            $this->handleFormPost($request);
        }
        else {
            $this->handleFormRefilling();

            $renderer = new MarkdownRenderingHelper();
            $this->assign('formPreamble', $renderer->doRender($form->getFormContent()));
            $this->assign('formUsernameHelp', $renderer->doRenderInline($form->getUsernameHelp()));
            $this->assign('formEmailHelp', $renderer->doRenderInline($form->getEmailHelp()));
            $this->assign('formCommentsHelp', $renderer->doRenderInline($form->getCommentHelp()));

            $this->setTemplate('request/request-form-dynamic.tpl');
        }
    }

    /**
     * @param RequestForm|null $form
     *
     * @return Request
     * @throws ApplicationLogicException
     */
    protected function createNewRequest(?RequestForm $form): Request
    {
        $database = $this->getDatabase();

        $request = new Request();

        if ($form === null) {
            $domain = 1;
        }
        else {
            $domain = $form->getDomain();
            $request->setOriginForm($form->getId());
        }

        $request->setQueue(RequestQueue::getDefaultQueue($database, $domain)->getId());
        $request->setDatabase($database);

        $request->setName(trim(WebRequest::postString('name')));
        $request->setEmail(WebRequest::postEmail('email'));

        $request->setIp(WebRequest::remoteAddress());
        $request->setForwardedIp(WebRequest::forwardedAddress());

        $request->setUserAgent(WebRequest::userAgent());

        return $request;
    }

    /**
     * @return Comment|null
     */
    private function createComment()
    {
        $commentText = WebRequest::postString('comments');
        if ($commentText === null || trim($commentText) === '') {
            return null;
        }

        $comment = new Comment();
        $comment->setDatabase($this->getDatabase());

        $comment->setVisibility('requester');
        $comment->setUser(null);
        $comment->setComment($commentText);

        return $comment;
    }

    /**
     * @param Request $request
     *
     * @return ValidationError[]
     */
    protected function validateRequest($request)
    {
        $validationHelper = $this->getRequestValidationHelper();

        // These are arrays of ValidationError.
        $nameValidation = $validationHelper->validateName($request);
        $emailValidation = $validationHelper->validateEmail($request, WebRequest::postEmail('emailconfirm'));
        $otherValidation = $validationHelper->validateOther($request);

        $validationErrors = array_merge($nameValidation, $emailValidation, $otherValidation);

        return $validationErrors;
    }

    /**
     * @param Request      $request
     *
     * @param Comment|null $comment
     *
     * @throws OptimisticLockFailedException
     * @throws Exception
     */
    protected function saveAsEmailConfirmation(Request $request, $comment)
    {
        $request->generateEmailConfirmationHash();
        $request->save();

        if ($comment !== null) {
            $comment->setRequest($request->getId());
            $comment->save();
        }

        $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp(
            $request->getIp(),
            $request->getForwardedIp());

        $this->assign("ip", $trustedIp);
        $this->assign("id", $request->getId());
        $this->assign("hash", $request->getEmailConfirm());

        // Sends the confirmation email to the user.
        // FIXME: domains
        /** @var Domain $domain */
        $domain = Domain::getById(1, $this->getDatabase());
        $this->getEmailHelper()->sendMail(
            $domain->getEmailReplyAddress(),
            $request->getEmail(),
            "[ACC #{$request->getId()}] English Wikipedia Account Request",
            $this->fetchTemplate('request/confirmation-mail.tpl'));

        $this->redirect('emailConfirmationRequired');
    }

    /**
     * @param Request      $request
     *
     * @param Comment|null $comment
     *
     * @throws OptimisticLockFailedException
     * @throws Exception
     */
    protected function saveWithoutEmailConfirmation(Request $request, $comment)
    {
        $request->setEmailConfirm(0); // fixme Since it can't be null
        $request->save();

        if ($comment !== null) {
            $comment->setRequest($request->getId());
            $comment->save();
        }

        $this->getNotificationHelper()->requestReceived($request);

        $this->redirect('requestSubmitted');
    }

    /**
     * @return RequestValidationHelper
     */
    protected function getRequestValidationHelper(): RequestValidationHelper
    {
        $banHelper = new BanHelper($this->getDatabase(), $this->getXffTrustProvider(), null);

        if ($this->validationHelper === null) {
            $this->validationHelper = new RequestValidationHelper(
                $banHelper,
                $this->getDatabase(),
                $this->getAntiSpoofProvider(),
                $this->getXffTrustProvider(),
                $this->getHttpHelper(),
                $this->getTorExitProvider(),
                $this->getSiteConfiguration());
        }

        return $this->validationHelper;
}

    /**
     * @param Request $request
     *
     * @return void
     * @throws OptimisticLockFailedException
     */
    protected function handleFormPost(Request $request): void
    {
        $comment = $this->createComment();

        $validationErrors = $this->validateRequest($request);

        if (count($validationErrors) > 0) {
            foreach ($validationErrors as $validationError) {
                SessionAlert::error($validationError->getErrorMessage());
            }

            // Preserve the data after an error
            WebRequest::setSessionContext('accountReq',
                array(
                    'username' => WebRequest::postString('name'),
                    'email'    => WebRequest::postEmail('email'),
                    'comments' => WebRequest::postString('comments'),
                )
            );

            // Validation error, bomb out early.
            $this->redirect();

            return;
        }

        // actually save the request to the database
        if ($this->getSiteConfiguration()->getEmailConfirmationEnabled()) {
            $this->saveAsEmailConfirmation($request, $comment);
        }
        else {
            $this->saveWithoutEmailConfirmation($request, $comment);
        }

        $this->getRequestValidationHelper()->postSaveValidations($request);
    }

    /**
     * @return void
     */
    protected function handleFormRefilling(): void
    {
        // set the form values from the session context
        $context = WebRequest::getSessionContext('accountReq');
        if ($context !== null && is_array($context)) {
            $this->assign('username', $context['username']);
            $this->assign('email', $context['email']);
            $this->assign('comments', $context['comments']);
        }

        // Clear it for a refresh
        WebRequest::setSessionContext('accountReq', null);
    }
}
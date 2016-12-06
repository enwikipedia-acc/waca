<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Request;

use Exception;
use Waca\DataObjects\Request;
use Waca\Helpers\BanHelper;
use Waca\SessionAlert;
use Waca\Tasks\PublicInterfacePageBase;
use Waca\Validation\RequestValidationHelper;
use Waca\Validation\ValidationError;
use Waca\WebRequest;

class PageRequestAccount extends PublicInterfacePageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        // dual mode page
        if (WebRequest::wasPosted()) {
            $request = $this->createNewRequest();

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
                $this->saveAsEmailConfirmation($request);
            }
            else {
                $this->saveWithoutEmailConfirmation($request);
            }
        }
        else {
            // set the form values from the session context
            $context = WebRequest::getSessionContext('accountReq');
            if ($context !== null && is_array($context)) {
                $this->assign('username', $context['username']);
                $this->assign('email', $context['email']);
                $this->assign('comments', $context['comments']);
            }

            // Clear it for a refresh
            WebRequest::setSessionContext('accountReq', null);

            $this->setTemplate('request/request-form.tpl');
        }
    }

    /**
     * @return Request
     */
    protected function createNewRequest()
    {
        $request = new Request();
        $request->setDatabase($this->getDatabase());

        $request->setName(WebRequest::postString('name'));
        $request->setEmail(WebRequest::postEmail('email'));
        $request->setComment(WebRequest::postString('comments'));

        $request->setIp(WebRequest::remoteAddress());
        $request->setForwardedIp(WebRequest::forwardedAddress());

        $request->setUserAgent(WebRequest::userAgent());

        return $request;
    }

    /**
     * @param Request $request
     *
     * @return ValidationError[]
     */
    protected function validateRequest($request)
    {
        $validationHelper = new RequestValidationHelper(
            new BanHelper($this->getDatabase()),
            $request,
            WebRequest::postEmail('emailconfirm'),
            $this->getDatabase(),
            $this->getAntiSpoofProvider(),
            $this->getXffTrustProvider(),
            $this->getHttpHelper(),
            $this->getSiteConfiguration()->getMediawikiWebServiceEndpoint(),
            $this->getSiteConfiguration()->getTitleBlacklistEnabled(),
            $this->getTorExitProvider());

        // These are arrays of ValidationError.
        $nameValidation = $validationHelper->validateName();
        $emailValidation = $validationHelper->validateEmail();
        $otherValidation = $validationHelper->validateOther();

        $validationErrors = array_merge($nameValidation, $emailValidation, $otherValidation);

        return $validationErrors;
    }

    /**
     * @param Request $request
     *
     * @throws Exception
     */
    protected function saveAsEmailConfirmation(Request $request)
    {
        $request->generateEmailConfirmationHash();
        $request->save();

        $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp(
            $request->getIp(),
            $request->getForwardedIp());

        $this->assign("ip", $trustedIp);
        $this->assign("id", $request->getId());
        $this->assign("hash", $request->getEmailConfirm());

        // Sends the confirmation email to the user.
        $this->getEmailHelper()->sendMail(
            $request->getEmail(),
            "[ACC #{$request->getId()}] English Wikipedia Account Request",
            $this->fetchTemplate('request/confirmation-mail.tpl'));

        $this->redirect('emailConfirmationRequired');
    }

    /**
     * @param Request $request
     *
     * @throws Exception
     */
    protected function saveWithoutEmailConfirmation(Request $request)
    {
        $request->setEmailConfirm(0); // fixme Since it can't be null
        $request->save();

        $this->getNotificationHelper()->requestReceived($request);

        $this->redirect('requestSubmitted');
    }
}
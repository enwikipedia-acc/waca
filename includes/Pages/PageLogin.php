<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

/**
 * Class PageLogin
 * @package Waca\Pages
 */
class PageLogin extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main()
    {
        // Start by enforcing HTTPS
        if ($this->getSiteConfiguration()->getUseStrictTransportSecurity() !== false) {
            if (WebRequest::isHttps()) {
                // Client can clearly use HTTPS, so let's enforce it for all connections.
                if (!headers_sent()) {
                    header("Strict-Transport-Security: max-age=15768000");
                }
            }
            else {
                // This is the login form, not the request form. We need protection here.
                $this->redirectUrl('https://' . WebRequest::serverName() . WebRequest::requestUri());

                return;
            }
        }

        if (WebRequest::wasPosted()) {
            // POST. Do some authentication.
            $this->validateCSRFToken();

            $user = null;
            try {
                $user = $this->getAuthenticatingUser();
            }
            catch (ApplicationLogicException $ex) {
                SessionAlert::error($ex->getMessage());
                $this->redirect('login');

                return;
            }

            // Touch force logout
            $user->setForceLogout(false);
            $user->save();

            if ($this->getSiteConfiguration()->getEnforceOAuth()) {
                if (!$user->isOAuthLinked()) {
                    $oauthHelper = $this->getOAuthHelper();

                    $requestToken = $oauthHelper->getRequestToken();
                    $user->setOAuthRequestToken($requestToken->key);
                    $user->setOAuthRequestSecret($requestToken->secret);
                    $user->save();

                    WebRequest::setPartialLogin($user);
                    $this->redirectUrl($oauthHelper->getAuthoriseUrl($requestToken->key));

                    return;
                }
            }

            // User is partially linked to OAuth. This is not allowed. Enforce it for this user.
            if ($user->getOnWikiName() === '##OAUTH##') {
                $oauthHelper = $this->getOAuthHelper();

                $requestToken = $oauthHelper->getRequestToken();
                $user->setOAuthRequestToken($requestToken->key);
                $user->setOAuthRequestSecret($requestToken->secret);
                $user->save();

                WebRequest::setPartialLogin($user);
                $this->redirectUrl($oauthHelper->getAuthoriseUrl($requestToken->key));

                return;
            }

            WebRequest::setLoggedInUser($user);

            $this->goBackWhenceYouCame($user);
        }
        else {
            // GET. Show the form
            $this->assignCSRFToken();
            $this->setTemplate("login.tpl");
        }
    }

    /**
     * @return User
     * @throws ApplicationLogicException
     */
    private function getAuthenticatingUser()
    {
        $username = WebRequest::postString("username");
        $password = WebRequest::postString("password");

        if ($username === null || $password === null || $username === "" || $password === "") {
            throw new ApplicationLogicException("No username/password specified");
        }

        /** @var User $user */
        $user = User::getByUsername($username, $this->getDatabase());

        if ($user == false || !$user->authenticate($password)) {
            throw new ApplicationLogicException("Authentication failed");
        }

        return $user;
    }

    protected function isProtectedPage()
    {
        return false;
    }

    /**
     * Redirect the user back to wherever they came from after a successful login
     *
     * @param User $user
     */
    private function goBackWhenceYouCame(User $user)
    {
        // Redirect to wherever the user came from
        $redirectDestination = WebRequest::clearPostLoginRedirect();
        if ($redirectDestination !== null) {
            $this->redirectUrl($redirectDestination);
        }
        else {
            if ($user->isNewUser()) {
                // home page isn't allowed, go to preferences instead
                $this->redirect('preferences');
            }
            else {
                // go to the home page
                $this->redirect('');
            }
        }
    }
}

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Session;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageOAuth extends InternalPageBase
{
    /**
     * Attach entry point
     *
     * must be posted, or will redirect to preferences
     */
    protected function attach()
    {
        if (!WebRequest::wasPosted()) {
            $this->redirect('preferences');

            return;
        }

        $this->validateCSRFToken();

        $oauthHelper = $this->getOAuthHelper();
        $user = User::getCurrent($this->getDatabase());

        $requestToken = $oauthHelper->getRequestToken();

        $user->setOAuthRequestToken($requestToken->key);
        $user->setOAuthRequestSecret($requestToken->secret);
        $user->save();

        $this->redirectUrl($oauthHelper->getAuthoriseUrl($requestToken->key));
    }

    /**
     * Detach account entry point
     */
    protected function detach()
    {
        if ($this->getSiteConfiguration()->getEnforceOAuth()) {
            throw new AccessDeniedException($this->getSecurityManager());
        }

        $user = User::getCurrent($this->getDatabase());

        $user->setOnWikiName($user->getOnWikiName());
        $user->setOAuthAccessSecret(null);
        $user->setOAuthAccessToken(null);
        $user->setOAuthRequestSecret(null);
        $user->setOAuthRequestToken(null);

        $user->clearOAuthData();

        $user->setForcelogout(true);

        $user->save();

        // force the user to log out
        Session::destroy();

        $this->redirect('login');
    }

    /**
     * Callback entry point
     */
    protected function callback()
    {
        $oauthToken = WebRequest::getString('oauth_token');
        $oauthVerifier = WebRequest::getString('oauth_verifier');

        $this->doCallbackValidation($oauthToken, $oauthVerifier);

        $user = User::getByRequestToken($oauthToken, $this->getDatabase());
        if ($user === false) {
            throw new ApplicationLogicException('Token not found in store, please try again');
        }

        $accessToken = $this->getOAuthHelper()->callbackCompleted(
            $user->getOAuthRequestToken(),
            $user->getOAuthRequestSecret(),
            $oauthVerifier);

        $user->setOAuthRequestSecret(null);
        $user->setOAuthRequestToken(null);
        $user->setOAuthAccessToken($accessToken->key);
        $user->setOAuthAccessSecret($accessToken->secret);

        // @todo we really should stop doing this kind of thing... it adds performance bottlenecks and breaks 3NF
        $user->setOnWikiName('##OAUTH##');

        $user->save();

        // OK, we're the same session that just did a partial login that was redirected to OAuth. Let's upgrade the
        // login to a full login
        if (WebRequest::getPartialLogin() === $user->getId()) {
            WebRequest::setLoggedInUser($user);
        }

        // My thinking is there are three cases here:
        //   a) new user => redirect to prefs - it's the only thing they can access other than stats
        //   b) existing user hit the connect button in prefs => redirect to prefs since it's where they were
        //   c) existing user logging in => redirect to wherever they came from
        $redirectDestination = WebRequest::clearPostLoginRedirect();
        if ($redirectDestination !== null && !$user->isNewUser()) {
            $this->redirectUrl($redirectDestination);
        }
        else {
            $this->redirect('preferences', null, null, 'internal.php');
        }
    }

    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->redirect('preferences');
    }

    /**
     * @param string $oauthToken
     * @param string $oauthVerifier
     *
     * @throws ApplicationLogicException
     */
    protected function doCallbackValidation($oauthToken, $oauthVerifier)
    {
        if ($oauthToken === null) {
            throw new ApplicationLogicException('No token provided');
        }

        if ($oauthVerifier === null) {
            throw new ApplicationLogicException('No oauth verifier provided.');
        }
    }
}

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use Exception;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\CurlException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Helpers\OAuthUserHelper;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageOAuthCallback extends InternalPageBase
{
    /**
     * @return bool
     */
    protected function isProtectedPage()
    {
        // This page is critical to ensuring OAuth functionality is operational.
        return false;
    }

    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        // This should never get hit except by URL manipulation.
        $this->redirect('');
    }

    /**
     * Registered endpoint for the account creation callback.
     *
     * If this ever gets hit, something is wrong somewhere.
     */
    protected function create()
    {
        throw new Exception('OAuth account creation endpoint triggered.');
    }

    /**
     * Callback entry point
     * @throws ApplicationLogicException
     * @throws OptimisticLockFailedException
     */
    protected function authorise()
    {
        $oauthToken = WebRequest::getString('oauth_token');
        $oauthVerifier = WebRequest::getString('oauth_verifier');

        $this->doCallbackValidation($oauthToken, $oauthVerifier);

        $database = $this->getDatabase();

        $user = OAuthUserHelper::findUserByRequestToken($oauthToken, $database);
        $oauth = new OAuthUserHelper($user, $database, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());

        try {
            $oauth->completeHandshake($oauthVerifier);
        }
        catch (CurlException $ex) {
            throw new ApplicationLogicException($ex->getMessage(), 0, $ex);
        }

        // OK, we're the same session that just did a partial login that was redirected to OAuth. Let's upgrade the
        // login to a full login
        if (WebRequest::getOAuthPartialLogin() === $user->getId()) {
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
     * @param string $oauthToken
     * @param string $oauthVerifier
     *
     * @throws ApplicationLogicException
     */
    private function doCallbackValidation($oauthToken, $oauthVerifier)
    {
        if ($oauthToken === null) {
            throw new ApplicationLogicException('No token provided');
        }

        if ($oauthVerifier === null) {
            throw new ApplicationLogicException('No oauth verifier provided.');
        }
    }
}
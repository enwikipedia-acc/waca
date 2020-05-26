<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use Exception;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\CurlException;
use Waca\Exceptions\OAuthException;
use Waca\Helpers\OAuthUserHelper;
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

        $database = $this->getDatabase();

        $this->validateCSRFToken();

        $oauthProtocolHelper = $this->getOAuthProtocolHelper();
        $user = User::getCurrent($database);
        $oauth = new OAuthUserHelper($user, $database, $oauthProtocolHelper, $this->getSiteConfiguration());

        try {
            $authoriseUrl = $oauth->getRequestToken();
            $this->redirectUrl($authoriseUrl);
        }
        catch (CurlException $ex) {
            throw new ApplicationLogicException($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * Detach account entry point
     * @throws Exception
     */
    protected function detach()
    {
        if ($this->getSiteConfiguration()->getEnforceOAuth()) {
            throw new AccessDeniedException($this->getSecurityManager());
        }

        $database = $this->getDatabase();
        $user = User::getCurrent($database);
        $oauth = new OAuthUserHelper($user, $database, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());

        try {
            $oauth->refreshIdentity();
        }
        catch (CurlException $ex) {
            // do nothing. The user's already revoked this access anyway.
        }
        catch (OAuthException $ex) {
            // do nothing. The user's already revoked this access anyway.
        }

        $oauth->detach();

        // TODO: figure out why we need to force logout after a detach.
        $user->setForcelogout(true);
        $user->save();

        // force the user to log out
        Session::destroy();

        $this->redirect('login');
    }

    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->redirect('preferences');
    }
}

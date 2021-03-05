<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use Waca\DataObjects\User;
use Waca\Helpers\OAuthUserHelper;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PagePreferences extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->setHtmlTitle('Preferences');

        $enforceOAuth = $this->getSiteConfiguration()->getEnforceOAuth();
        $database = $this->getDatabase();
        $user = User::getCurrent($database);

        // Dual mode
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $user->setWelcomeSig(WebRequest::postString('sig'));
            $user->setEmailSig(WebRequest::postString('emailsig'));
            $user->setAbortPref(WebRequest::postBoolean('abortpref') ? 1 : 0);
            $this->setCreationMode($user);

            $newSkin = WebRequest::postString('skintype');
            if ($newSkin === 'main' || $newSkin === 'alt' || $newSkin === 'auto') {
                $user->setSkin($newSkin);
            }

            $email = WebRequest::postEmail('email');
            if ($email !== null) {
                $user->setEmail($email);
            }

            $user->save();
            SessionAlert::success("Preferences updated!");

            $this->redirect('');
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate('preferences/prefs.tpl');
            $this->assign("enforceOAuth", $enforceOAuth);

            $this->assign('canManualCreate',
                $this->barrierTest(User::CREATION_MANUAL, $user, 'RequestCreation'));
            $this->assign('canOauthCreate',
                $this->barrierTest(User::CREATION_OAUTH, $user, 'RequestCreation'));
            $this->assign('canBotCreate',
                $this->barrierTest(User::CREATION_BOT, $user, 'RequestCreation'));

            $oauth = new OAuthUserHelper($user, $database, $this->getOAuthProtocolHelper(),
                $this->getSiteConfiguration());
            $this->assign('oauth', $oauth);

            $identity = null;
            if ($oauth->isFullyLinked()) {
                $identity = $oauth->getIdentity(true);
            }

            $this->assign('identity', $identity);
            $this->assign('graceTime', $this->getSiteConfiguration()->getOauthIdentityGraceTime());
        }
    }

    protected function refreshOAuth()
    {
        if (!WebRequest::wasPosted()) {
            $this->redirect('preferences');

            return;
        }

        $database = $this->getDatabase();
        $oauth = new OAuthUserHelper(User::getCurrent($database), $database, $this->getOAuthProtocolHelper(),
            $this->getSiteConfiguration());

        // token is for old consumer, run through the approval workflow again
        if ($oauth->getIdentity(true)->getAudience() !== $this->getSiteConfiguration()->getOAuthConsumerToken()) {
            $authoriseUrl = $oauth->getRequestToken();
            $this->redirectUrl($authoriseUrl);

            return;
        }

        if ($oauth->isFullyLinked()) {
            $oauth->refreshIdentity();
        }

        $this->redirect('preferences');

        return;
    }

    /**
     * @param User $user
     */
    protected function setCreationMode(User $user)
    {
        // if the user is selecting a creation mode that they are not allowed, do nothing.
        // this has the side effect of allowing them to keep a selected mode that either has been changed for them,
        // or that they have kept from when they previously had certain access.
        $creationMode = WebRequest::postInt('creationmode');
        if ($this->barrierTest($creationMode, $user, 'RequestCreation')) {
            $user->setCreationMode($creationMode);
        }
    }
}

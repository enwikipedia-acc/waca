<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\PreferenceManager;
use Waca\Pages\PageMain;
use Waca\Security\RoleConfiguration;
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
        $preferencesManager = PreferenceManager::getForCurrent($database);

        // Dual mode
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $preferencesManager->setLocalPreference(PreferenceManager::PREF_EMAIL_SIGNATURE, WebRequest::postString('emailsig'));
            $preferencesManager->setLocalPreference(PreferenceManager::PREF_SKIP_JS_ABORT, WebRequest::postBoolean('abortpref') ? 1 : 0);
            $this->setCreationMode($user, $preferencesManager);

            $newSkin = WebRequest::postString('skintype');
            if ($newSkin === 'main' || $newSkin === 'alt' || $newSkin === 'auto') {
                $preferencesManager->setLocalPreference(PreferenceManager::PREF_SKIN, $newSkin);
            }

            $email = WebRequest::postEmail('email');
            if ($email !== null) {
                $user->setEmail($email);
            }

            $user->save();
            SessionAlert::success("Preferences updated!");

            if ($this->barrierTest(RoleConfiguration::MAIN, $user, PageMain::class)) {
                $this->redirect('');
            }
            else {
                $this->redirect('preferences');
            }
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate('preferences/prefs.tpl');

            // FIXME: domains!
            /** @var Domain $domain */
            $domain = Domain::getById(1, $this->getDatabase());
            $this->assign('mediawikiScriptPath', $domain->getWikiArticlePath());

            $this->assign("enforceOAuth", $enforceOAuth);

            $this->assign('emailSignature', $preferencesManager->getPreference(PreferenceManager::PREF_EMAIL_SIGNATURE));
            $this->assign('creationMode', $preferencesManager->getPreference(PreferenceManager::PREF_CREATION_MODE));
            $this->assign('skin', $preferencesManager->getPreference(PreferenceManager::PREF_SKIN));
            $this->assign('skipJsAbort', $preferencesManager->getPreference(PreferenceManager::PREF_SKIP_JS_ABORT));

            $this->assign('canManualCreate',
                $this->barrierTest(PreferenceManager::CREATION_MANUAL, $user, 'RequestCreation'));
            $this->assign('canOauthCreate',
                $this->barrierTest(PreferenceManager::CREATION_OAUTH, $user, 'RequestCreation'));
            $this->assign('canBotCreate',
                $this->barrierTest(PreferenceManager::CREATION_BOT, $user, 'RequestCreation'));

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
    private function setCreationMode(User $user, PreferenceManager $preferenceManager)
    {
        // if the user is selecting a creation mode that they are not allowed, do nothing.
        // this has the side effect of allowing them to keep a selected mode that either has been changed for them,
        // or that they have kept from when they previously had certain access.
        $creationMode = WebRequest::postInt('creationmode');
        if ($this->barrierTest($creationMode, $user, 'RequestCreation')) {
            $preferenceManager->setLocalPreference(PreferenceManager::PREF_CREATION_MODE, $creationMode);
        }
    }
}

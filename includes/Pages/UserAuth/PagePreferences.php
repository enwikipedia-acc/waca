<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\PreferenceManager;
use Waca\Pages\PageMain;
use Waca\Security\RoleConfigurationBase;
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

            $this->setPreference($preferencesManager,PreferenceManager::PREF_EMAIL_SIGNATURE, 'emailSignature');
            $this->setPreferenceWithValue($preferencesManager,PreferenceManager::PREF_SKIP_JS_ABORT, 'skipJsAbort', WebRequest::postBoolean('skipJsAbort') ? 1 : 0);
            $this->setPreferenceWithValue($preferencesManager,PreferenceManager::PREF_QUEUE_HELP, 'showQueueHelp', WebRequest::postBoolean('showQueueHelp') ? 1 : 0);
            $this->setCreationMode($user, $preferencesManager);
            $this->setSkin($preferencesManager);
            $preferencesManager->setGlobalPreference(PreferenceManager::PREF_DEFAULT_DOMAIN, WebRequest::postInt('defaultDomain'));

            $email = WebRequest::postEmail('email');
            if ($email !== null) {
                $user->setEmail($email);
            }

            $user->save();
            SessionAlert::success("Preferences updated!");

            if ($this->barrierTest(RoleConfigurationBase::MAIN, $user, PageMain::class)) {
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

            $this->assignPreference($preferencesManager, PreferenceManager::PREF_EMAIL_SIGNATURE, 'emailSignature', false);
            $this->assignPreference($preferencesManager, PreferenceManager::PREF_CREATION_MODE, 'creationMode', false);
            $this->assignPreference($preferencesManager, PreferenceManager::PREF_SKIN, 'skin', true);
            $this->assignPreference($preferencesManager, PreferenceManager::PREF_SKIP_JS_ABORT, 'skipJsAbort', false);
            $this->assignPreference($preferencesManager, PreferenceManager::PREF_QUEUE_HELP, 'showQueueHelp', false, true);
            $this->assignPreference($preferencesManager, PreferenceManager::PREF_DEFAULT_DOMAIN, 'defaultDomain', true);

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

    private function assignPreference(
        PreferenceManager $preferencesManager,
        string $preference,
        string $fieldName,
        bool $defaultGlobal,
        $defaultValue = null
    ): void {
        $this->assign($fieldName, $preferencesManager->getPreference($preference) ?? $defaultValue);
        $this->assign($fieldName . 'Global', $preferencesManager->isGlobalPreference($preference) ?? $defaultGlobal);
    }

    private function setPreferenceWithValue(
        PreferenceManager $preferencesManager,
        string $preferenceName,
        string $fieldName,
        $value
    ): void {
        $globalDefinition = WebRequest::postBoolean($fieldName . 'Global');
        if ($globalDefinition) {
            $preferencesManager->setGlobalPreference($preferenceName, $value);
        }
        else {
            $preferencesManager->setLocalPreference($preferenceName, $value);
        }
    }

    private function setPreference(
        PreferenceManager $preferencesManager,
        string $preferenceName,
        string $fieldName
    ): void {
        $this->setPreferenceWithValue($preferencesManager, $preferenceName, $fieldName, WebRequest::postString($fieldName));
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

    private function setCreationMode(User $user, PreferenceManager $preferenceManager)
    {
        // if the user is selecting a creation mode that they are not allowed, do nothing.
        // this has the side effect of allowing them to keep a selected mode that either has been changed for them,
        // or that they have kept from when they previously had certain access.
        // This setting is only settable locally, as ACLs may change between domains.
        $creationMode = WebRequest::postInt('creationMode');

        if ($creationMode === null) {
            return;
        }

        if ($this->barrierTest($creationMode, $user, 'RequestCreation')) {
            $preferenceManager->setLocalPreference(PreferenceManager::PREF_CREATION_MODE, WebRequest::postString('creationMode'));
        }
    }

    private function setSkin(PreferenceManager $preferencesManager): void
    {
        $newSkin = WebRequest::postString('skin');
        $allowedSkins = ['main', 'alt', 'auto'];
        if (in_array($newSkin, $allowedSkins)) {
            $this->setPreference($preferencesManager, PreferenceManager::PREF_SKIN, 'skin');
        }
    }
}

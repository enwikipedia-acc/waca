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
            $user->setAbortPref(WebRequest::getBoolean('sig') ? 1 : 0);

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

            $oauth = new OAuthUserHelper($user, $database, $this->getOAuthProtocolHelper(),
                $this->getSiteConfiguration());
            $this->assign('oauth', $oauth);

            $identity = null;
            if ($oauth->isFullyLinked()) {
                $identity = $oauth->getIdentity();
            }

            $this->assign('identity', $identity);
            $this->assign('graceTime', $this->getSiteConfiguration()->getOauthIdentityGraceTime());
        }
    }
}

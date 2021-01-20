<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth\Login;

use PDO;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OAuthException;
use Waca\Helpers\OAuthUserHelper;
use Waca\PdoDatabase;
use Waca\Security\AuthenticationManager;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

abstract class LoginCredentialPageBase extends InternalPageBase
{
    /** @var User */
    protected $partialUser = null;
    protected $nextPageMap = array(
        'yubikeyotp' => 'otp',
        'totp'       => 'otp',
        'scratch'    => 'otp',
        'webauthn'   => 'webauthn'
    );
    protected $names = array(
        'yubikeyotp' => 'Yubikey OTP',
        'totp'       => 'TOTP (phone code generator)',
        'scratch'    => 'scratch token',
        'webauthn'   => 'WebAuthn hardware authentication'
    );

    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        if (!$this->enforceHttps()) {
            return;
        }

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $database = $this->getDatabase();
            try {
                list($partialId, $partialStage, $partialToken) = WebRequest::getAuthPartialLogin();

                if ($partialStage === null) {
                    $partialStage = 1;
                }

                if ($partialId === null) {
                    $username = WebRequest::postString('username');

                    if ($username === null || trim($username) === '') {
                        throw new ApplicationLogicException('No username specified.');
                    }

                    $user = User::getByUsername($username, $database);
                }
                else {
                    $user = User::getById($partialId, $database);
                }

                if ($user === false) {
                    throw new ApplicationLogicException("Authentication failed");
                }

                $authMan = new AuthenticationManager($database, $this->getSiteConfiguration(),
                    $this->getHttpHelper());

                $credential = $this->getProviderCredentials();

                $authResult = $authMan->authenticate($user, $credential, $partialStage);

                if ($authResult === AuthenticationManager::AUTH_FAIL) {
                    throw new ApplicationLogicException("Authentication failed");
                }

                if ($authResult === AuthenticationManager::AUTH_REQUIRE_NEXT_STAGE) {
                    $this->processJumpNextStage($user, $partialStage, $database);

                    return;
                }

                if ($authResult === AuthenticationManager::AUTH_OK) {
                    $this->processLoginSuccess($user);

                    return;
                }
            }
            catch (ApplicationLogicException $ex) {
                WebRequest::clearAuthPartialLogin();

                SessionAlert::error($ex->getMessage());
                $this->redirect('login');

                return;
            }
        }
        else {
            $this->assign('showSignIn', true);

            $this->setupPartial();
            $this->assignCSRFToken();
            $this->providerSpecificSetup();
        }
    }

    protected function isProtectedPage()
    {
        return false;
    }

    /**
     * Enforces HTTPS on the login form
     *
     * @return bool
     */
    private function enforceHttps()
    {
        if ($this->getSiteConfiguration()->getUseStrictTransportSecurity() !== false) {
            if (WebRequest::isHttps()) {
                // Client can clearly use HTTPS, so let's enforce it for all connections.
                $this->headerQueue[] = "Strict-Transport-Security: max-age=15768000";
            }
            else {
                // This is the login form, not the request form. We need protection here.
                $this->redirectUrl('https://' . WebRequest::serverName() . WebRequest::requestUri());

                return false;
            }
        }

        return true;
    }

    protected abstract function providerSpecificSetup();

    protected function setupPartial()
    {
        $database = $this->getDatabase();

        // default stuff
        $this->assign('alternatives', array()); // 'u2f' => array('U2F token'), 'otp' => array('TOTP', 'scratch', 'yubiotp')));

        // is this stage one?
        list($partialId, $partialStage, $partialToken) = WebRequest::getAuthPartialLogin();
        if ($partialStage === null || $partialId === null) {
            WebRequest::clearAuthPartialLogin();
        }

        // Check to see if we have a partial login in progress
        $username = null;
        if ($partialId !== null) {
            // Yes, enforce this username
            $this->partialUser = User::getById($partialId, $database);
            $username = $this->partialUser->getUsername();

            $this->setupAlternates($this->partialUser, $partialStage, $database);
        }
        else {
            // No, see if we've preloaded a username
            $preloadUsername = WebRequest::getString('tplUsername');
            if ($preloadUsername !== null) {
                $username = $preloadUsername;
            }
        }

        if ($partialStage === null) {
            $partialStage = 1;
        }

        $this->assign('partialStage', $partialStage);
        $this->assign('username', $username);
    }

    /**
     * Redirect the user back to wherever they came from after a successful login
     *
     * @param User $user
     */
    protected function goBackWhenceYouCame(User $user)
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

    private function processLoginSuccess(User $user)
    {
        // Touch force logout
        $user->setForceLogout(false);
        $user->save();

        $oauth = new OAuthUserHelper($user, $this->getDatabase(), $this->getOAuthProtocolHelper(),
            $this->getSiteConfiguration());

        if ($oauth->isFullyLinked()) {
            try {
                // Reload the user's identity ticket.
                $oauth->refreshIdentity();

                // Check for blocks
                if ($oauth->getIdentity()->getBlocked()) {
                    // blocked!
                    SessionAlert::error("You are currently blocked on-wiki. You will not be able to log in until you are unblocked.");
                    $this->redirect('login');

                    return;
                }
            }
            catch (OAuthException $ex) {
                // Oops. Refreshing ticket failed. Force a re-auth.
                $authoriseUrl = $oauth->getRequestToken();
                WebRequest::setOAuthPartialLogin($user);
                $this->redirectUrl($authoriseUrl);

                return;
            }
        }

        if (($this->getSiteConfiguration()->getEnforceOAuth() && !$oauth->isFullyLinked())
            || $oauth->isPartiallyLinked()
        ) {
            $authoriseUrl = $oauth->getRequestToken();
            WebRequest::setOAuthPartialLogin($user);
            $this->redirectUrl($authoriseUrl);

            return;
        }

        WebRequest::setLoggedInUser($user);

        $this->goBackWhenceYouCame($user);
    }

    protected abstract function getProviderCredentials();

    /**
     * @param User        $user
     * @param int         $partialStage
     * @param PdoDatabase $database
     *
     * @throws ApplicationLogicException
     */
    private function processJumpNextStage(User $user, $partialStage, PdoDatabase $database)
    {
        WebRequest::setAuthPartialLogin($user->getId(), $partialStage + 1);

        $sql = 'SELECT type FROM credential WHERE user = :user AND factor = :stage AND disabled = 0 ORDER BY priority';
        $statement = $database->prepare($sql);
        $statement->execute(array(':user' => $user->getId(), ':stage' => $partialStage + 1));
        $nextStage = $statement->fetchColumn();
        $statement->closeCursor();

        if (!isset($this->nextPageMap[$nextStage])) {
            throw new ApplicationLogicException('Unknown page handler for next authentication stage.');
        }

        $this->redirect("login/" . $this->nextPageMap[$nextStage]);
    }

    private function setupAlternates(User $user, $partialStage, PdoDatabase $database)
    {
        // get the providers available
        $sql = 'SELECT type FROM credential WHERE user = :user AND factor = :stage AND disabled = 0';
        $statement = $database->prepare($sql);
        $statement->execute(array(':user' => $user->getId(), ':stage' => $partialStage));
        $alternates = $statement->fetchAll(PDO::FETCH_COLUMN);

        $types = array();
        foreach ($alternates as $item) {
            $type = $this->nextPageMap[$item];
            if (!isset($types[$type])) {
                $types[$type] = array();
            }

            $types[$type][] = $item;
        }

        $userOptions = array();
        if (get_called_class() !== PageOtpLogin::class) {
            $userOptions = array_merge($userOptions, $this->setupUserOptionsForType($types, 'otp', $userOptions));
        }

        if (get_called_class() !== PageWebAuthnLogin::class) {
            $userOptions = array_merge($userOptions, $this->setupUserOptionsForType($types, 'webauthn', $userOptions));
        }

        $this->assign('alternatives', $userOptions);
    }

    /**
     * @param $types
     * @param $type
     * @param $userOptions
     *
     * @return mixed
     */
    private function setupUserOptionsForType($types, $type, $userOptions)
    {
        if (isset($types[$type])) {
            $options = $types[$type];

            array_walk($options, function(&$val) {
                $val = $this->names[$val];
            });

            $userOptions[$type] = $options;
        }

        return $userOptions;
    }
}

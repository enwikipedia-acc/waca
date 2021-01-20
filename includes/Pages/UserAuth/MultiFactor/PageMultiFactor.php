<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth\MultiFactor;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use ParagonIE\ConstantTime\Base32;
use Throwable;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PdoDatabase;
use Waca\Security\CredentialProviders\ICredentialProvider;
use Waca\Security\CredentialProviders\PasswordCredentialProvider;
use Waca\Security\CredentialProviders\ScratchTokenCredentialProvider;
use Waca\Security\CredentialProviders\TotpCredentialProvider;
use Waca\Security\CredentialProviders\WebAuthnCredentialProvider;
use Waca\Security\CredentialProviders\YubikeyOtpCredentialProvider;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageMultiFactor extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $yubikeyOtpCredentialProvider = new YubikeyOtpCredentialProvider($database, $this->getSiteConfiguration(),
            $this->getHttpHelper());
        $this->assign('yubikeyOtpIdentity', $yubikeyOtpCredentialProvider->getYubikeyData($currentUser->getId()));
        $this->assign('yubikeyOtpEnrolled', $yubikeyOtpCredentialProvider->userIsEnrolled($currentUser->getId()));

        $totpCredentialProvider = new TotpCredentialProvider($database, $this->getSiteConfiguration());
        $this->assign('totpEnrolled', $totpCredentialProvider->userIsEnrolled($currentUser->getId()));

        $webauthnCredentialProvider = new WebAuthnCredentialProvider($database, $this->getSiteConfiguration());
        $this->assign('webAuthnEnrolled', $webauthnCredentialProvider->userIsEnrolled($currentUser->getId()));
        $this->assign('webAuthnTokens', $webauthnCredentialProvider->listEnrolledTokens($currentUser->getId()));

        $scratchCredentialProvider = new ScratchTokenCredentialProvider($database, $this->getSiteConfiguration());
        $this->assign('scratchEnrolled', $scratchCredentialProvider->userIsEnrolled($currentUser->getId()));
        $this->assign('scratchRemaining', $scratchCredentialProvider->getRemaining($currentUser->getId()));

        $this->assign('allowedTotp', $this->barrierTest('enableTotp', $currentUser));
        $this->assign('allowedYubikey', $this->barrierTest('enableYubikeyOtp', $currentUser));
        $this->assign('allowedWebAuthn', $this->barrierTest('enableWebAuthn', $currentUser));

        $this->setTemplate('mfa/mfa.tpl');
    }

    protected function enableYubikeyOtp()
    {
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $otpCredentialProvider = new YubikeyOtpCredentialProvider($database,
            $this->getSiteConfiguration(), $this->getHttpHelper());

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $passwordCredentialProvider = new PasswordCredentialProvider($database,
                $this->getSiteConfiguration());

            $password = WebRequest::postString('password');
            $otp = WebRequest::postString('otp');

            $result = $passwordCredentialProvider->authenticate($currentUser, $password);

            if ($result) {
                try {
                    $otpCredentialProvider->setCredential($currentUser, 2, $otp);
                    SessionAlert::success('Enabled YubiKey OTP.');

                    $scratchProvider = new ScratchTokenCredentialProvider($database, $this->getSiteConfiguration());
                    if($scratchProvider->getRemaining($currentUser->getId()) < 3) {
                        $scratchProvider->setCredential($currentUser, 2, null);
                        $tokens = $scratchProvider->getTokens();
                        $this->assign('tokens', $tokens);
                        $this->setTemplate('mfa/regenScratchTokens.tpl');
                        return;
                    }
                }
                catch (ApplicationLogicException $ex) {
                    SessionAlert::error('Error enabling YubiKey OTP: ' . $ex->getMessage());
                }

                $this->redirect('multiFactor');
            }
            else {
                SessionAlert::error('Error enabling YubiKey OTP - invalid credentials.');
                $this->redirect('multiFactor');
            }
        }
        else {
            if ($otpCredentialProvider->userIsEnrolled($currentUser->getId())) {
                // user is not enrolled, we shouldn't have got here.
                throw new ApplicationLogicException('User is already enrolled in the selected MFA mechanism');
            }

            $this->assignCSRFToken();
            $this->setTemplate('mfa/enableYubikey.tpl');
        }
    }

    protected function disableYubikeyOtp()
    {
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $otpCredentialProvider = new YubikeyOtpCredentialProvider($database,
            $this->getSiteConfiguration(), $this->getHttpHelper());

        $factorType = 'YubiKey OTP';

        $this->deleteCredential($database, $currentUser, $otpCredentialProvider, $factorType);
    }

    protected function enableTotp()
    {
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $otpCredentialProvider = new TotpCredentialProvider($database, $this->getSiteConfiguration());

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            // used for routing only, not security
            $stage = WebRequest::postString('stage');

            if ($stage === "auth") {
                $password = WebRequest::postString('password');

                $passwordCredentialProvider = new PasswordCredentialProvider($database,
                    $this->getSiteConfiguration());
                $result = $passwordCredentialProvider->authenticate($currentUser, $password);

                if ($result) {
                    $otpCredentialProvider->setCredential($currentUser, 2, null);

                    $provisioningUrl = $otpCredentialProvider->getProvisioningUrl($currentUser);

                    $renderer = new ImageRenderer(
                        new RendererStyle(256),
                        new SvgImageBackEnd()
                    );

                    $writer = new Writer($renderer);
                    $svg = $writer->writeString($provisioningUrl);

                    $this->assign('svg', $svg);
                    $this->assign('secret', $otpCredentialProvider->getSecret($currentUser));

                    $this->assignCSRFToken();
                    $this->setTemplate('mfa/enableTotpEnroll.tpl');

                    return;
                }
                else {
                    SessionAlert::error('Error enabling TOTP - invalid credentials.');
                    $this->redirect('multiFactor');

                    return;
                }
            }

            if ($stage === "enroll") {
                // we *must* have a defined credential already here,
                if ($otpCredentialProvider->isPartiallyEnrolled($currentUser)) {
                    $otp = WebRequest::postString('otp');
                    $result = $otpCredentialProvider->verifyEnable($currentUser, $otp);

                    if ($result) {
                        SessionAlert::success('Enabled TOTP.');

                        $scratchProvider = new ScratchTokenCredentialProvider($database, $this->getSiteConfiguration());
                        if($scratchProvider->getRemaining($currentUser->getId()) < 3) {
                            $scratchProvider->setCredential($currentUser, 2, null);
                            $tokens = $scratchProvider->getTokens();
                            $this->assign('tokens', $tokens);
                            $this->setTemplate('mfa/regenScratchTokens.tpl');
                            return;
                        }
                    }
                    else {
                        $otpCredentialProvider->deleteCredential($currentUser);
                        SessionAlert::error('Error enabling TOTP: invalid token provided');
                    }


                    $this->redirect('multiFactor');
                    return;
                }
                else {
                    SessionAlert::error('Error enabling TOTP - no enrollment found or enrollment expired.');
                    $this->redirect('multiFactor');

                    return;
                }
            }

            // urgh, dunno what happened, but it's not something expected.
            throw new ApplicationLogicException();
        }
        else {
            if ($otpCredentialProvider->userIsEnrolled($currentUser->getId())) {
                // user is not enrolled, we shouldn't have got here.
                throw new ApplicationLogicException('User is already enrolled in the selected MFA mechanism');
            }

            $this->assignCSRFToken();

            $this->assign('alertmessage', 'To enable your multi-factor credentials, please prove you are who you say you are by providing the information below.');
            $this->assign('alertheader', 'Provide credentials');
            $this->assign('continueText', 'Verify password');
            $this->setTemplate('mfa/enableAuth.tpl');
        }
    }

    protected function disableTotp()
    {
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $otpCredentialProvider = new TotpCredentialProvider($database, $this->getSiteConfiguration());

        $factorType = 'TOTP';

        $this->deleteCredential($database, $currentUser, $otpCredentialProvider, $factorType);
    }

    protected function enableWebAuthn() {
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $credentialProvider = new WebAuthnCredentialProvider($database, $this->getSiteConfiguration());

        if(WebRequest::wasPosted()) {
            // used for routing only, not security
            $stage = WebRequest::postString('stage');

            if ($stage === "auth") {
                $this->validateCSRFToken();

                $password = WebRequest::postString('password');

                $passwordCredentialProvider = new PasswordCredentialProvider($database,
                    $this->getSiteConfiguration());
                $result = $passwordCredentialProvider->authenticate($currentUser, $password);

                if ($result) {
                    $enrollmentToken = Base32::encodeUpper(openssl_random_pseudo_bytes(30));
                    WebRequest::setSessionContext('webauthn-enroll', $enrollmentToken);
                    WebRequest::setSessionContext('webauthn-enroll-timeout', time() + 300);
                    $this->assign('enrollment', $enrollmentToken);

                    $this->addJs('/resources/auth/webauthn-register.js', 'module');
                    $this->setTemplate('mfa/enableWebAuthnEnroll.tpl');

                    return;
                }
                else {
                    SessionAlert::error('Error enabling WebAuthn - invalid credentials.');
                    $this->redirect('multiFactor');

                    return;
                }
            }
            else if($stage == "failure") {
                $this->redirect('multiFactor');
                return;
            }
            else if($stage == "success") {
                SessionAlert::success('Enabled WebAuthn.');

                $scratchProvider = new ScratchTokenCredentialProvider($database, $this->getSiteConfiguration());
                if($scratchProvider->getRemaining($currentUser->getId()) < 3) {
                    $scratchProvider->setCredential($currentUser, 2, null);
                    $tokens = $scratchProvider->getTokens();
                    $this->assign('tokens', $tokens);
                    $this->setTemplate('mfa/regenScratchTokens.tpl');
                    return;
                }

                $this->redirect('multiFactor');
                return;
            }
            else {
                // This is managed by JS, so we need to be careful here.
                try {
                    $rawData = file_get_contents("php://input");
                    $data = json_decode($rawData, true);

                    if (isset($data['enrollment'])) {
                        $enrollmentToken = WebRequest::getSessionContext('webauthn-enroll');
                        $enrollmentTokenTimeout = WebRequest::getSessionContext('webauthn-enroll-timeout');

                        WebRequest::setSessionContext('webauthn-enroll-tokenname', $data['tokenName']);

                        if ($enrollmentToken !== $data['enrollment']) {
                            throw new ApplicationLogicException('Enrollment failed.');
                        }

                        if ($enrollmentTokenTimeout < time()) {
                            // timeout, sorry.
                            throw new ApplicationLogicException('Enrollment failed.');
                        }

                        $registrationData = $credentialProvider->beginEnrollment($currentUser);

                        $this->headerQueue[] = 'Content-Type: application/json';
                        $this->assign('content', $registrationData);
                        $this->setTemplate('raw.tpl');

                        return;
                    }

                    if (isset($data['id'])) {
                        $credentialProvider->setCredential($currentUser, 2, $rawData);
                        $this->assign('content', json_encode(["status"=> "success"]));
                        $this->setTemplate('raw.tpl');
                        return;
                    }

                    throw new ApplicationLogicException('Enrollment failed.');
                } catch (Throwable $ex) {
                    SessionAlert::error($ex->getMessage());
                    throw new ApplicationLogicException("", 0, $ex);
                }
            }
        }
        else {
            $this->assignCSRFToken();

            $this->assign('alertmessage', 'To enable your multi-factor credentials, please prove you are who you say you are by providing the information below.');
            $this->assign('alertheader', 'Provide credentials');
            $this->assign('continueText', 'Verify password');
            $this->setTemplate('mfa/enableAuth.tpl');
        }
    }

    protected function disableWebAuthn() {
        if(!WebRequest::wasPosted()) {
            $this->redirect('multiFactor');
        }

        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $otpCredentialProvider = new WebAuthnCredentialProvider($database, $this->getSiteConfiguration());

        if (!$otpCredentialProvider->userIsEnrolled($currentUser->getId())) {
            // user is not enrolled, we shouldn't have got here.
            throw new ApplicationLogicException('User is not enrolled in the selected MFA mechanism');
        }

        if(WebRequest::postString("password") === null) {
            $this->assignCSRFToken();
            $this->assign('otpType', "WebAuthn authenticator");
            $this->assign('identifier', WebRequest::postString('publicKeyId'));
            $this->setTemplate('mfa/disableOtp.tpl');
        } else {
            $passwordCredentialProvider = new PasswordCredentialProvider($database,
                $this->getSiteConfiguration());

            $this->validateCSRFToken();

            $password = WebRequest::postString('password');
            $publicKeyId = WebRequest::postString('identifier');
            $result = $passwordCredentialProvider->authenticate($currentUser, $password);

            if ($result) {
                $otpCredentialProvider->deleteToken($currentUser, $publicKeyId);
                SessionAlert::success('Disabled WebAuthn authenticator.');
                $this->redirect('multiFactor');
            }
            else {
                SessionAlert::error('Error disabling WebAuthn authenticator - invalid credentials.');
                $this->redirect('multiFactor');
            }

        }
    }

    protected function scratch()
    {
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $passwordCredentialProvider = new PasswordCredentialProvider($database,
                $this->getSiteConfiguration());

            $otpCredentialProvider = new ScratchTokenCredentialProvider($database,
                $this->getSiteConfiguration());

            $password = WebRequest::postString('password');

            $result = $passwordCredentialProvider->authenticate($currentUser, $password);

            if ($result) {
                $otpCredentialProvider->setCredential($currentUser, 2, null);
                $tokens = $otpCredentialProvider->getTokens();
                $this->assign('tokens', $tokens);
                $this->setTemplate('mfa/regenScratchTokens.tpl');
            }
            else {
                SessionAlert::error('Error refreshing scratch tokens - invalid credentials.');
                $this->redirect('multiFactor');
            }
        }
        else {
            $this->assignCSRFToken();

            $this->assign('alertmessage', 'To regenerate your emergency scratch tokens, please prove you are who you say you are by providing the information below. Note that continuing will invalidate all remaining scratch tokens, and provide a set of new ones.');
            $this->assign('alertheader', 'Re-generate scratch tokens');
            $this->assign('continueText', 'Regenerate Scratch Tokens');

            $this->setTemplate('mfa/enableAuth.tpl');
        }
    }

    /**
     * @param PdoDatabase         $database
     * @param User                $currentUser
     * @param ICredentialProvider $otpCredentialProvider
     * @param string              $factorType
     *
     * @throws ApplicationLogicException
     */
    private function deleteCredential(
        PdoDatabase $database,
        User $currentUser,
        ICredentialProvider $otpCredentialProvider,
        $factorType
    ) {
        if (WebRequest::wasPosted()) {
            $passwordCredentialProvider = new PasswordCredentialProvider($database,
                $this->getSiteConfiguration());

            $this->validateCSRFToken();

            $password = WebRequest::postString('password');
            $result = $passwordCredentialProvider->authenticate($currentUser, $password);

            if ($result) {
                $otpCredentialProvider->deleteCredential($currentUser);
                SessionAlert::success('Disabled ' . $factorType . '.');
                $this->redirect('multiFactor');
            }
            else {
                SessionAlert::error('Error disabling ' . $factorType . ' - invalid credentials.');
                $this->redirect('multiFactor');
            }
        }
        else {
            if (!$otpCredentialProvider->userIsEnrolled($currentUser->getId())) {
                // user is not enrolled, we shouldn't have got here.
                throw new ApplicationLogicException('User is not enrolled in the selected MFA mechanism');
            }

            $this->assignCSRFToken();
            $this->assign('otpType', $factorType);
            $this->setTemplate('mfa/disableOtp.tpl');
        }
    }
}

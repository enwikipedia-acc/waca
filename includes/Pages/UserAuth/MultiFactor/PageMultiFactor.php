<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\UserAuth\MultiFactor;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PdoDatabase;
use Waca\Security\CredentialProviders\ICredentialProvider;
use Waca\Security\CredentialProviders\PasswordCredentialProvider;
use Waca\Security\CredentialProviders\ScratchTokenCredentialProvider;
use Waca\Security\CredentialProviders\TotpCredentialProvider;
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

        $scratchCredentialProvider = new ScratchTokenCredentialProvider($database, $this->getSiteConfiguration());
        $this->assign('scratchEnrolled', $scratchCredentialProvider->userIsEnrolled($currentUser->getId()));
        $this->assign('scratchRemaining', $scratchCredentialProvider->getRemaining($currentUser->getId()));

        $this->assign('allowedTotp', $this->barrierTest('enableTotp', $currentUser));
        $this->assign('allowedYubikey', $this->barrierTest('enableYubikeyOtp', $currentUser));

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
                    if ($scratchProvider->getRemaining($currentUser->getId()) < 3) {
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
                        if ($scratchProvider->getRemaining($currentUser->getId()) < 3) {
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

            $this->assign('alertmessage', 'To enable your multi-factor credentials, please prove you are who you say you are by providing your tool password below.');
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

            $this->assign('alertmessage', 'To regenerate your emergency scratch tokens, please prove you are who you say you are by providing your tool password below. Note that continuing will invalidate all remaining scratch tokens, and provide a set of new ones.');
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

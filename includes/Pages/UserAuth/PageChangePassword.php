<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Security\CredentialProviders\PasswordCredentialProvider;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageChangePassword extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->setHtmlTitle('Change Password');

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            try {
                $oldPassword = WebRequest::postString('password');
                $newPassword = WebRequest::postString('newpassword');
                $newPasswordConfirmation = WebRequest::postString('newpasswordconfirm');

                $user = User::getCurrent($this->getDatabase());
                if (!$user instanceof User) {
                    throw new ApplicationLogicException('User not found');
                }

                $this->validateNewPassword($oldPassword, $newPassword, $newPasswordConfirmation, $user);

                $passwordProvider = new PasswordCredentialProvider($this->getDatabase(), $this->getSiteConfiguration());
                $passwordProvider->setCredential($user, 1, $newPassword);
            }
            catch (ApplicationLogicException $ex) {
                SessionAlert::error($ex->getMessage());
                $this->redirect('changePassword');

                return;
            }

            SessionAlert::success('Password changed successfully!');

            $this->redirect('preferences');
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate('preferences/changePassword.tpl');
            $this->addJs("/vendor/dropbox/zxcvbn/dist/zxcvbn.js");
        }
    }

    /**
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $newPasswordConfirmation
     * @param User   $user
     *
     * @throws ApplicationLogicException
     */
    protected function validateNewPassword($oldPassword, $newPassword, $newPasswordConfirmation, User $user)
    {
        if ($oldPassword === null || $newPassword === null || $newPasswordConfirmation === null) {
            throw new ApplicationLogicException('All three fields must be completed to change your password');
        }

        if ($newPassword !== $newPasswordConfirmation) {
            throw new ApplicationLogicException('Your new passwords did not match!');
        }

        // TODO: adapt for MFA support
        $passwordProvider = new PasswordCredentialProvider($this->getDatabase(), $this->getSiteConfiguration());
        if (!$passwordProvider->authenticate($user, $oldPassword)) {
            throw new ApplicationLogicException('The password you entered was incorrect.');
        }
    }
}

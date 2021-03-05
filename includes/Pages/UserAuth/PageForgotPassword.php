<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use ParagonIE\ConstantTime\Base32;
use DateTimeImmutable;
use Waca\DataObjects\Credential;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PdoDatabase;
use Waca\Security\CredentialProviders\PasswordCredentialProvider;
use Waca\Security\EncryptionHelper;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageForgotPassword extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     *
     * This is the forgotten password reset form
     * @category Security-Critical
     */
    protected function main()
    {
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $username = WebRequest::postString('username');
            $email = WebRequest::postEmail('email');
            $database = $this->getDatabase();

            if ($username === null || trim($username) === "" || $email === null || trim($email) === "") {
                throw new ApplicationLogicException("Both username and email address must be specified!");
            }

            $user = User::getByUsername($username, $database);
            $this->sendResetMail($user, $email);

            SessionAlert::success('<strong>Your password reset request has been completed.</strong> If the details you have provided match our records, you should receive an email shortly.');

            $this->redirect('login');
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate('forgot-password/forgotpw.tpl');
        }
    }

    /**
     * Sends a reset email if the user is authenticated
     *
     * @param User|boolean $user  The user located from the database, or false. Doesn't really matter, since we do the
     *                            check anyway within this method and silently skip if we don't have a user.
     * @param string       $email The provided email address
     */
    private function sendResetMail($user, $email)
    {
        // If the user isn't found, or the email address is wrong, skip sending the details silently.
        if (!$user instanceof User) {
            return;
        }

        if (strtolower($user->getEmail()) === strtolower($email)) {
            $clientIp = $this->getXffTrustProvider()
                ->getTrustedClientIp(WebRequest::remoteAddress(), WebRequest::forwardedAddress());

            $this->cleanExistingTokens($user);

            $hash = Base32::encodeUpper(openssl_random_pseudo_bytes(30));

            $encryptionHelper = new EncryptionHelper($this->getSiteConfiguration());

            $cred = new Credential();
            $cred->setDatabase($this->getDatabase());
            $cred->setFactor(-1);
            $cred->setUserId($user->getId());
            $cred->setType('reset');
            $cred->setData($encryptionHelper->encryptData($hash));
            $cred->setVersion(0);
            $cred->setDisabled(0);
            $cred->setTimeout(new DateTimeImmutable('+ 1 hour'));
            $cred->setPriority(9);
            $cred->save();

            $this->assign("user", $user);
            $this->assign("hash", $hash);
            $this->assign("remoteAddress", $clientIp);

            $emailContent = $this->fetchTemplate('forgot-password/reset-mail.tpl');

            $this->getEmailHelper()->sendMail($user->getEmail(), "WP:ACC password reset", $emailContent);
        }
    }

    /**
     * Entry point for the reset action
     *
     * This is the reset password part of the form.
     * @category Security-Critical
     */
    protected function reset()
    {
        $si = WebRequest::getString('si');
        $id = WebRequest::getString('id');

        if ($si === null || trim($si) === "" || $id === null || trim($id) === "") {
            throw new ApplicationLogicException("Link not valid, please ensure it has copied correctly");
        }

        $database = $this->getDatabase();
        $user = $this->getResettingUser($id, $database, $si);

        // Dual mode
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            try {
                $this->doReset($user);
                $this->cleanExistingTokens($user);
            }
            catch (ApplicationLogicException $ex) {
                SessionAlert::error($ex->getMessage());
                $this->redirect('forgotPassword', 'reset', array('si' => $si, 'id' => $id));

                return;
            }
        }
        else {
            $this->assignCSRFToken();
            $this->assign('user', $user);
            $this->setTemplate('forgot-password/forgotpwreset.tpl');
            $this->addJs("/vendor/dropbox/zxcvbn/dist/zxcvbn.js");
        }
    }

    /**
     * Gets the user resetting their password from the database, or throwing an exception if that is not possible.
     *
     * @param integer     $id       The ID of the user to retrieve
     * @param PdoDatabase $database The database object to use
     * @param string      $si       The reset hash provided
     *
     * @return User
     * @throws ApplicationLogicException
     */
    private function getResettingUser($id, $database, $si)
    {
        $user = User::getById($id, $database);

        if ($user === false || $user->isCommunityUser()) {
            throw new ApplicationLogicException("Password reset failed. Please try again.");
        }

        $statement = $database->prepare("SELECT * FROM credential WHERE type = 'reset' AND user = :user;");
        $statement->execute([':user' => $user->getId()]);

        /** @var Credential $credential */
        $credential = $statement->fetchObject(Credential::class);

        $statement->closeCursor();

        if ($credential === false) {
            throw new ApplicationLogicException("Password reset failed. Please try again.");
        }

        $credential->setDatabase($database);

        $encryptionHelper = new EncryptionHelper($this->getSiteConfiguration());
        if ($encryptionHelper->decryptData($credential->getData()) != $si) {
            throw new ApplicationLogicException("Password reset failed. Please try again.");
        }

        if ($credential->getTimeout() < new DateTimeImmutable()) {
            $credential->delete();
            throw new ApplicationLogicException("Password reset token expired. Please try again.");
        }

        return $user;
    }

    /**
     * Performs the setting of the new password
     *
     * @param User $user The user to set the password for
     *
     * @throws ApplicationLogicException
     */
    private function doReset(User $user)
    {
        $pw = WebRequest::postString('pw');
        $pw2 = WebRequest::postString('pw2');

        if ($pw !== $pw2) {
            throw new ApplicationLogicException('Passwords do not match!');
        }

        $passwordCredentialProvider = new PasswordCredentialProvider($user->getDatabase(), $this->getSiteConfiguration());
        $passwordCredentialProvider->setCredential($user, 1, $pw);

        SessionAlert::success('You may now log in!');
        $this->redirect('login');
    }

    protected function isProtectedPage()
    {
        return false;
    }

    /**
     * @param $user
     */
    private function cleanExistingTokens($user): void
    {
        // clean out existing reset tokens
        $statement = $this->getDatabase()->prepare("SELECT * FROM credential WHERE type = 'reset' AND user = :user;");
        $statement->execute([':user' => $user->getId()]);
        $existing = $statement->fetchAll(PdoDatabase::FETCH_CLASS, Credential::class);

        foreach ($existing as $c) {
            $c->setDatabase($this->getDatabase());
            $c->delete();
        }
    }
}

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use Waca\DataObjects\Credential;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;
use Waca\SessionAlert;
use Waca\SiteConfiguration;
use Wikimedia\CommonPasswords\CommonPasswords;
use ZxcvbnPhp\Zxcvbn;

class PasswordCredentialProvider extends CredentialProviderBase
{
    const PASSWORD_COST = 10;
    const PASSWORD_ALGO = PASSWORD_BCRYPT;

    public function __construct(PdoDatabase $database, SiteConfiguration $configuration)
    {
        parent::__construct($database, $configuration, 'password');
    }

    public function authenticate(User $user, $data)
    {
        $storedData = $this->getCredentialData($user->getId());
        if ($storedData === null) {
            // No available credential matching these parameters
            return false;
        }

        if ($storedData->getVersion() !== 2) {
            // Non-2 versions are not supported.
            return false;
        }

        if (!password_verify($data, $storedData->getData())) {
            return false;
        }

        if (password_needs_rehash($storedData->getData(), self::PASSWORD_ALGO,
            array('cost' => self::PASSWORD_COST))) {
            try {
                $this->reallySetCredential($user, $storedData->getFactor(), $data);
            }
            catch (OptimisticLockFailedException $e) {
                // optimistic lock failed, but no biggie. We'll catch it on the next login.
            }
        }

        $strengthTester = new Zxcvbn();
        $strength = $strengthTester->passwordStrength($data, [$user->getUsername(), $user->getOnWikiName(), $user->getEmail()]);

        /*  0 means the password is extremely guessable (within 10^3 guesses), dictionary words like 'password' or 'mother' score a 0
            1 is still very guessable (guesses < 10^6), an extra character on a dictionary word can score a 1
            2 is somewhat guessable (guesses < 10^8), provides some protection from unthrottled online attacks
            3 is safely unguessable (guesses < 10^10), offers moderate protection from offline slow-hash scenario
            4 is very unguessable (guesses >= 10^10) and provides strong protection from offline slow-hash scenario         */

        if ($strength['score'] <= 1 || CommonPasswords::isCommon($data) || mb_strlen($data) < 8) {
            // prevent login for extremely weak passwords
            // at this point the user has authenticated via password, so they *know* it's weak.
            SessionAlert::error('Your password is too weak to permit login. Please choose the "forgotten your password" option below and set a new one.', null);
            return false;
        }

        $this->revokePasswordResetTokens($user->getId());

        return true;
    }

    /**
     * @param User   $user
     * @param int    $factor
     * @param string $password
     *
     * @throws OptimisticLockFailedException
     */
    private function reallySetCredential(User $user, int $factor, string $password) : void {
        $storedData = $this->getCredentialData($user->getId());

        if ($storedData === null) {
            $storedData = $this->createNewCredential($user);
        }

        $storedData->setData(password_hash($password, self::PASSWORD_ALGO, array('cost' => self::PASSWORD_COST)));
        $storedData->setFactor($factor);
        $storedData->setVersion(2);

        $storedData->save();
    }

    /**
     * @param User   $user
     * @param int    $factor
     * @param string $password
     *
     * @throws ApplicationLogicException
     * @throws OptimisticLockFailedException
     */
    public function setCredential(User $user, $factor, $password)
    {
        if (CommonPasswords::isCommon($password)) {
            throw new ApplicationLogicException("Your new password is listed in the top 100,000 passwords. Please choose a stronger one.", null);
        }

        $strengthTester = new Zxcvbn();
        $strength = $strengthTester->passwordStrength($password, [$user->getUsername(), $user->getOnWikiName(), $user->getEmail()]);

        /*  0 means the password is extremely guessable (within 10^3 guesses), dictionary words like 'password' or 'mother' score a 0
            1 is still very guessable (guesses < 10^6), an extra character on a dictionary word can score a 1
            2 is somewhat guessable (guesses < 10^8), provides some protection from unthrottled online attacks
            3 is safely unguessable (guesses < 10^10), offers moderate protection from offline slow-hash scenario
            4 is very unguessable (guesses >= 10^10) and provides strong protection from offline slow-hash scenario         */

        if ($strength['score'] <= 2 || mb_strlen($password) < 8) {
            throw new ApplicationLogicException("Your new password is too weak. Please choose a stronger one.", null);
        }

        if ($strength['score'] <= 3) {
            SessionAlert::warning("Your new password is not as strong as it could be. Consider replacing it with a stronger password.", null);
        }

        $this->reallySetCredential($user, $factor, $password);
    }

    /**
     * @param User $user
     *
     * @throws ApplicationLogicException
     */
    public function deleteCredential(User $user)
    {
        throw new ApplicationLogicException('Deletion of password credential is not allowed.');
    }

    private function revokePasswordResetTokens(int $userId)
    {
        $statement = $this->getDatabase()->prepare("SELECT * FROM credential WHERE type = 'reset' AND user = :user;");
        $statement->execute([':user' => $userId]);
        $existing = $statement->fetchAll(PdoDatabase::FETCH_CLASS, Credential::class);

        foreach ($existing as $c) {
            $c->setDatabase($this->getDatabase());
            $c->delete();
        }
    }
}

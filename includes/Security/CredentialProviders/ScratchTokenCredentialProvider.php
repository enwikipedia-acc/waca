<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use ParagonIE\ConstantTime\Base32;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;
use Waca\Security\EncryptionHelper;
use Waca\SessionAlert;
use Waca\SiteConfiguration;
use Waca\WebRequest;

class ScratchTokenCredentialProvider extends CredentialProviderBase
{
    /** @var EncryptionHelper */
    private $encryptionHelper;
    /** @var array the tokens generated in the last generation round. */
    private $generatedTokens;

    /**
     * ScratchTokenCredentialProvider constructor.
     *
     * @param PdoDatabase       $database
     * @param SiteConfiguration $configuration
     */
    public function __construct(PdoDatabase $database, SiteConfiguration $configuration)
    {
        parent::__construct($database, $configuration, 'scratch');
        $this->encryptionHelper = new EncryptionHelper($configuration);
    }

    /**
     * Validates a user-provided credential
     *
     * @param User   $user The user to test the authentication against
     * @param string $data The raw credential data to be validated
     *
     * @return bool
     * @throws ApplicationLogicException|OptimisticLockFailedException
     */
    public function authenticate(User $user, $data)
    {
        if (is_array($data)) {
            return false;
        }

        $storedData = $this->getCredentialData($user->getId());

        if ($storedData === null) {
            throw new ApplicationLogicException('Credential data not found');
        }

        $scratchTokens = unserialize($this->encryptionHelper->decryptData($storedData->getData()));

        $usedToken = null;
        foreach ($scratchTokens as $scratchToken) {
            if (password_verify($data, $scratchToken)) {
                $usedToken = $scratchToken;
                SessionAlert::quick("Hey, it looks like you used a scratch token to log in. Would you like to change your multi-factor authentication configuration?", 'alert-warning');
                WebRequest::setPostLoginRedirect($this->getConfiguration()->getBaseUrl() . "/internal.php/multiFactor");
                break;
            }
        }

        if ($usedToken === null) {
            return false;
        }

        $scratchTokens = array_diff($scratchTokens, [$usedToken]);

        $storedData->setData($this->encryptionHelper->encryptData(serialize($scratchTokens)));
        $storedData->save();

        return true;
    }

    /**
     * @param User   $user   The user the credential belongs to
     * @param int    $factor The factor this credential provides
     * @param string $data   Unused.
     *
     * @throws OptimisticLockFailedException
     */
    public function setCredential(User $user, $factor, $data)
    {
        $plaintextScratch = array();
        $storedScratch = array();
        for ($i = 0; $i < 5; $i++) {
            $token = Base32::encodeUpper(openssl_random_pseudo_bytes(10));
            $plaintextScratch[] = $token;

            $storedScratch[] = password_hash(
                $token,
                PasswordCredentialProvider::PASSWORD_ALGO,
                array('cost' => PasswordCredentialProvider::PASSWORD_COST)
            );
        }

        $storedData = $this->getCredentialData($user->getId(), null);

        if ($storedData !== null) {
            $storedData->delete();
        }

        $storedData = $this->createNewCredential($user);

        $storedData->setData($this->encryptionHelper->encryptData(serialize($storedScratch)));
        $storedData->setFactor($factor);
        $storedData->setVersion(1);
        $storedData->setPriority(9);

        $storedData->save();
        $this->generatedTokens = $plaintextScratch;
    }

    /**
     * Gets the count of remaining valid tokens
     *
     * @param int $userId
     *
     * @return int
     */
    public function getRemaining($userId)
    {
        $storedData = $this->getCredentialData($userId);

        if ($storedData === null) {
            return 0;
        }

        $scratchTokens = unserialize($this->encryptionHelper->decryptData($storedData->getData()));

        return count($scratchTokens);
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        if ($this->generatedTokens != null) {
            return $this->generatedTokens;
        }

        return array();
    }
}

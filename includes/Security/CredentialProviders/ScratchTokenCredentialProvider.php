<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use Base32\Base32;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PdoDatabase;
use Waca\Security\EncryptionHelper;
use Waca\SiteConfiguration;

class ScratchTokenCredentialProvider extends CredentialProviderBase
{
    /** @var EncryptionHelper */
    private $encryptionHelper;

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
     * @throws ApplicationLogicException
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

        $i = array_search($data, $scratchTokens);

        if($i === false) {
            return false;
        }

        unset($scratchTokens[$i]);

        $storedData->setData($this->encryptionHelper->encryptData(serialize($scratchTokens)));
        $storedData->save();

        return true;
    }

    /**
     * @param User   $user   The user the credential belongs to
     * @param int    $factor The factor this credential provides
     * @param string $data   Unused.
     */
    public function setCredential(User $user, $factor, $data)
    {
        $scratch = array();
        for ($i = 0; $i < 5; $i++) {
            $scratch[] = Base32::encode(openssl_random_pseudo_bytes(10));
        }

        $storedData = $this->getCredentialData($user->getId(), null);

        if ($storedData !== null) {
            $storedData->delete();
        }

        $storedData = $this->createNewCredential($user);

        $storedData->setData($this->encryptionHelper->encryptData(serialize($scratch)));
        $storedData->setFactor($factor);
        $storedData->setVersion(1);
        $storedData->setPriority(9);

        $storedData->save();
    }

    /**
     * @param int $userId
     *
     * @return int
     * @throws ApplicationLogicException
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
     * @param int $userId
     *
     * @return int
     * @throws ApplicationLogicException
     */
    public function getTokens($userId)
    {
        $storedData = $this->getCredentialData($userId);

        if ($storedData === null) {
            return 0;
        }

        $scratchTokens = unserialize($this->encryptionHelper->decryptData($storedData->getData()));

        return $scratchTokens;
    }
}
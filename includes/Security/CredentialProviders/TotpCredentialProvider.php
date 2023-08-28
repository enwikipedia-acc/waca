<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use DateTimeImmutable;
use OTPHP\Factory;
use OTPHP\TOTP;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PdoDatabase;
use Waca\Security\EncryptionHelper;
use Waca\SiteConfiguration;

class TotpCredentialProvider extends CredentialProviderBase
{
    /** @var EncryptionHelper */
    private $encryptionHelper;

    /**
     * TotpCredentialProvider constructor.
     *
     * @param PdoDatabase       $database
     * @param SiteConfiguration $configuration
     */
    public function __construct(PdoDatabase $database, SiteConfiguration $configuration)
    {
        parent::__construct($database, $configuration, 'totp');
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

        $provisioningUrl = $this->encryptionHelper->decryptData($storedData->getData());
        $totp = Factory::loadFromProvisioningUri($provisioningUrl);

        return $totp->verify($data, null, 2);
    }

    public function verifyEnable(User $user, $data)
    {
        $storedData = $this->getCredentialData($user->getId(), true);

        if ($storedData === null) {
            throw new ApplicationLogicException('Credential data not found');
        }

        $provisioningUrl = $this->encryptionHelper->decryptData($storedData->getData());
        $totp = Factory::loadFromProvisioningUri($provisioningUrl);

        $result = $totp->verify($data, null, 2);

        if ($result && $storedData->getTimeout() > new DateTimeImmutable()) {
            $storedData->setDisabled(0);
            $storedData->setPriority(5);
            $storedData->setTimeout(null);
            $storedData->save();
        }

        return $result;
    }

    /**
     * @param User   $user   The user the credential belongs to
     * @param int    $factor The factor this credential provides
     * @param string $data   Unused here, due to there being no user-provided data. We provide the user with the secret.
     */
    public function setCredential(User $user, $factor, $data)
    {
        $issuer = 'ACC - ' . $this->getConfiguration()->getIrcNotificationsInstance();
        $totp = TOTP::create();
        $totp->setLabel($user->getUsername());
        $totp->setIssuer($issuer);

        $storedData = $this->getCredentialData($user->getId(), null);

        if ($storedData !== null) {
            $storedData->delete();
        }

        $storedData = $this->createNewCredential($user);

        $storedData->setData($this->encryptionHelper->encryptData($totp->getProvisioningUri()));
        $storedData->setFactor($factor);
        $storedData->setTimeout(new DateTimeImmutable('+ 1 hour'));
        $storedData->setDisabled(1);
        $storedData->setVersion(1);

        $storedData->save();
    }

    public function getProvisioningUrl(User $user)
    {
        $storedData = $this->getCredentialData($user->getId(), true);

        if ($storedData->getTimeout() < new DateTimeImmutable()) {
            $storedData->delete();
            $storedData = null;
        }

        if ($storedData === null) {
            throw new ApplicationLogicException('Credential data not found');
        }

        return $this->encryptionHelper->decryptData($storedData->getData());
    }

    public function isPartiallyEnrolled(User $user)
    {
        $storedData = $this->getCredentialData($user->getId(), true);

        if ($storedData->getTimeout() < new DateTimeImmutable()) {
            $storedData->delete();

            return false;
        }

        if ($storedData === null) {
            return false;
        }

        return true;
    }

    public function getSecret(User $user)
    {
        $totp = Factory::loadFromProvisioningUri($this->getProvisioningUrl($user));

        return $totp->getSecret();
    }
}

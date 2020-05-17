<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use DateTimeImmutable;
use u2flib_server\Error;
use u2flib_server\U2F;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;
use Waca\WebRequest;

class U2FCredentialProvider extends CredentialProviderBase
{
    /** @var U2F */
    private $u2f;

    /**
     * U2FCredentialProvider constructor.
     *
     * @param PdoDatabase       $database
     * @param SiteConfiguration $configuration
     */
    public function __construct(PdoDatabase $database, SiteConfiguration $configuration)
    {
        parent::__construct($database, $configuration, 'u2f');

        $appId = 'https://' . WebRequest::httpHost();
        $this->u2f = new U2F($appId);
    }

    /**
     * Validates a user-provided credential
     *
     * @param User   $user The user to test the authentication against
     * @param string $data The raw credential data to be validated
     *
     * @return bool
     * @throws OptimisticLockFailedException
     */
    public function authenticate(User $user, $data)
    {
        if (!is_array($data)) {
            return false;
        }

        list($authenticate, $request, $isU2F) = $data;

        if ($isU2F !== 'u2f') {
            return false;
        }

        $storedData = $this->getCredentialData($user->getId(), false);
        $registrations = json_decode($storedData->getData());

        try {
            $updatedRegistration = $this->u2f->doAuthenticate($request, array($registrations), $authenticate);
            $storedData->setData(json_encode($updatedRegistration));
            $storedData->save();
        }
        catch (Error $ex) {
            return false;
        }

        return true;
    }

    public function enable(User $user, $request, $u2fData)
    {
        $registrationData = $this->u2f->doRegister($request, $u2fData);

        $storedData = $this->getCredentialData($user->getId(), true);

        if ($storedData === null) {
            throw new ApplicationLogicException('Credential data not found');
        }

        if ($storedData->getTimeout() > new DateTimeImmutable()) {
            $storedData->setData(json_encode($registrationData));
            $storedData->setDisabled(0);
            $storedData->setTimeout(null);
            $storedData->save();
        }
    }

    /**
     * @param User   $user   The user the credential belongs to
     * @param int    $factor The factor this credential provides
     * @param string $data   Unused here, due to multi-stage enrollment
     */
    public function setCredential(User $user, $factor, $data)
    {
        $storedData = $this->getCredentialData($user->getId(), null);

        if ($storedData !== null) {
            $storedData->delete();
        }

        $storedData = $this->createNewCredential($user);

        $storedData->setData(null);
        $storedData->setFactor($factor);
        $storedData->setTimeout(new DateTimeImmutable('+ 1 hour'));
        $storedData->setDisabled(1);
        $storedData->setPriority(4);
        $storedData->setVersion(1);

        $storedData->save();
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

    public function getRegistrationData()
    {
        return $this->u2f->getRegisterData();
    }

    public function getAuthenticationData(User $user)
    {
        $storedData = $this->getCredentialData($user->getId(), false);
        $registrations = json_decode($storedData->getData());

        $authenticateData = $this->u2f->getAuthenticateData(array($registrations));

        return $authenticateData;
    }
}

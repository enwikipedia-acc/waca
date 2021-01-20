<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use ParagonIE\ConstantTime\Base32;
use Throwable;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PdoDatabase;
use Waca\Security\CredentialProviders\WebAuthn\PublicKeyCredentialUserEntity;
use Waca\Security\EncryptionHelper;
use Waca\SiteConfiguration;
use Waca\WebRequest;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use WebAuthn\PublicKeyCredentialUserEntity as WebAuthnPublicKeyCredentialUserEntity;
use Webauthn\Server;

class WebAuthnCredentialProvider extends CredentialProviderBase implements PublicKeyCredentialSourceRepository
{
    /** @var Server WebAuthnServer */
    private $server;
    /** @var EncryptionHelper */
    private $encryptionHelper;
    /** @var User */
    private $partialLoginUser;

    /**
     * WebAuthnCredentialProvider constructor.
     *
     * @param PdoDatabase $database
     * @param SiteConfiguration $configuration
     */
    public function __construct(PdoDatabase $database, SiteConfiguration $configuration)
    {
        parent::__construct($database, $configuration, 'webauthn');
        $this->encryptionHelper = new EncryptionHelper($configuration);

        $rpEntity = new PublicKeyCredentialRpEntity(
            'English Wikipedia Account Creation Tool (' . $this->getConfiguration()
                ->getIrcNotificationsInstance() . ')',
            parse_url($this->getConfiguration()->getBaseUrl())['host']
        );

        $this->server = new Server($rpEntity, $this, null);
    }

    public function listEnrolledTokens(int $userId): array
    {
        if (!$this->userIsEnrolled($userId)) {
            return [];
        }

        $credential = $this->getCredentialData($userId, null);
        $credentialData = unserialize($this->encryptionHelper->decryptData($credential->getData()))['tokenMetadata'];

        return $credentialData;
    }

    public function deleteToken(User $user, string $publicKeyId): void
    {
        $credential = $this->getCredentialData($user->getId(), null);
        $credentialData = unserialize($this->encryptionHelper->decryptData($credential->getData()));

        if (isset($credentialData['tokenMetadata'][$publicKeyId])) {
            unset($credentialData['tokenMetadata'][$publicKeyId]);
        }
        if (isset($credentialData['tokens'][$publicKeyId])) {
            unset($credentialData['tokens'][$publicKeyId]);
        }

        if (count($credentialData['tokenMetadata']) != count($credentialData['tokens'])) {
            // something has gone horribly wrong.
            throw new ApplicationLogicException("Mismatch between registered tokens and metadata");
        }

        if (count($credentialData['token']) == 0) {
            $this->deleteCredential($user);
        }
        else {
            $credential->setData($this->encryptionHelper->encryptData(serialize($credentialData)));
            $credential->save();
        }
    }

    // from Waca\Security\CredentialProviders\ICredentialProvider
    public function authenticate(User $user, $data)
    {
        if ($data === "/+") {
            return false;
        }

        [, , $partialToken] = WebRequest::getAuthPartialLogin();
        WebRequest::setAuthPartialLoginToken('');

        if ($data === $partialToken) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * Overrides from Waca\Security\CredentialProviders\ICredentialProvider
     */
    public function setCredential(User $user, $factor, $data)
    {
        $creationOptions = WebRequest::getWebAuthnOptions();

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $serverRequest = $creator->fromGlobals();

        try {
            $publicKeyCredentialSource = $this->server->loadAndCheckAttestationResponse(
                $data, $creationOptions, $serverRequest);

            $credential = $this->getCredentialData($user->getId(), null);

            if ($credential === null) {
                $credential = $this->createNewCredential($user);
                $credential->setFactor($factor);
                $credential->setPriority(4);
                $credential->setVersion(1);
                $credential->setData($this->encryptionHelper->encryptData(serialize([
                    'tokenMetadata' => [],
                    'tokens'        => [],
                ])));
            }

            // save the token metadata first; this isn't (and can't be) managed by the webauthn library calls
            $credentialData = unserialize($this->encryptionHelper->decryptData($credential->getData()));
            $credentialData['tokenMetadata'][base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId())] = [
                'publicKeyId' => base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()),
                'tokenName'   => WebRequest::getSessionContext('webauthn-enroll-tokenname'),
            ];
            $credential->setData($this->encryptionHelper->encryptData(serialize($credentialData)));
            $credential->save();

            $this->saveCredentialSource($publicKeyCredentialSource);
        }
        catch (Throwable $exception) {
            throw new ApplicationLogicException("Enrollment failed", 0, $exception);
        }
    }

    /**
     * First step of enrollment
     *
     * @param User $user
     *
     * @return false|string
     */
    public function beginEnrollment(User $user)
    {
        $userEntity = new PublicKeyCredentialUserEntity($user);

        $credentialSources = $this->findAllForUserEntity($userEntity);
        $excludeCredentials = array_map(function(PublicKeyCredentialSource $credential) {
            return $credential->getPublicKeyCredentialDescriptor();
        }, $credentialSources);

        $publicKeyCredentialCreationOptions = $this->server->generatePublicKeyCredentialCreationOptions(
            $userEntity,
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $excludeCredentials
        );

        WebRequest::setWebAuthnOptions($publicKeyCredentialCreationOptions);

        return json_encode($publicKeyCredentialCreationOptions);
    }

    public function beginAuthentication(User $user)
    {
        $userEntity = new PublicKeyCredentialUserEntity($user);
        $credentialSources = $this->findAllForUserEntity($userEntity);

        $allowedCredentials = array_map(function(PublicKeyCredentialSource $credential) {
            return $credential->getPublicKeyCredentialDescriptor();
        }, $credentialSources);

        $publicKeyCredentialRequestOptions = $this->server->generatePublicKeyCredentialRequestOptions(
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            $allowedCredentials
        );

        WebRequest::setWebAuthnOptions($publicKeyCredentialRequestOptions);

        return json_encode($publicKeyCredentialRequestOptions);
    }

    public function completeAuthentication($data, User $user)
    {
        $this->partialLoginUser = $user;
        $requestOptions = WebRequest::getWebAuthnOptions();
        $userEntity = new PublicKeyCredentialUserEntity($user);

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $serverRequest = $creator->fromGlobals();

        try {
            $publicKeyCredentialSource = $this->server->loadAndCheckAssertionResponse($data, $requestOptions,
                $userEntity, $serverRequest);

            $token = Base32::encodeUpper(openssl_random_pseudo_bytes(30));
            WebRequest::setAuthPartialLoginToken($token);

            $credential = $this->getCredentialData($user->getId(), null);
            $credentialData = unserialize($this->encryptionHelper->decryptData($credential->getData()));
            $credentialData['tokenMetadata'][base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId())]['lastUsed'] = time();
            $credential->setData($this->encryptionHelper->encryptData(serialize($credentialData)));
            $credential->save();

            return ['token' => $token, 'source' => $publicKeyCredentialSource];
        }
        catch (Throwable $exception) {
            return ['token' => '/+'];
        }
    }

    /**
     *
     * from Webauthn\PublicKeyCredentialSourceRepository
     *
     * @param string $publicKeyCredentialId
     *
     * @return PublicKeyCredentialSource|null
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $currentUser = User::getCurrent($this->getDatabase());

        $credential = $this->getCredentialData($currentUser->getId(), null);

        if ($currentUser->isCommunityUser() && $this->partialLoginUser !== null) {
            // We're in the middle of a login, so use the partial-login user instead.
            $credential = $this->getCredentialData($this->partialLoginUser->getId(), null);
        }

        if ($credential === null) {
            return null;
        }

        $credentialData = unserialize($this->encryptionHelper->decryptData($credential->getData()))['tokens'];

        if (isset($credentialData[base64_encode($publicKeyCredentialId)])) {
            return $credentialData[base64_encode($publicKeyCredentialId)];
        }

        return null;
    }

    /**
     * Gets all WebAuthn registrations for the supplied user
     *
     * from Webauthn\PublicKeyCredentialSourceRepository
     *
     * @param WebAuthnPublicKeyCredentialUserEntity $userEntity
     *
     * @return array
     */
    public function findAllForUserEntity(WebAuthnPublicKeyCredentialUserEntity $userEntity): array
    {
        $credential = $this->getCredentialData($userEntity->getId(), null);

        if ($credential === null) {
            // Nothing stored yet
            return [];
        }

        return unserialize($this->encryptionHelper->decryptData($credential->getData()))['tokens'];
    }

    // from Webauthn\PublicKeyCredentialSourceRepository
    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $credential = $this->getCredentialData($publicKeyCredentialSource->getUserHandle(), null);

        if ($credential === null) {
            throw new ApplicationLogicException("Existing credential data not found, cannot save.");
        }

        $credentialData = unserialize($this->encryptionHelper->decryptData($credential->getData()));
        $credentialData['tokens'][base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId())] = $publicKeyCredentialSource;

        $credential->setData($this->encryptionHelper->encryptData(serialize($credentialData)));
        $credential->setDisabled(0);
        $credential->save();
    }
}
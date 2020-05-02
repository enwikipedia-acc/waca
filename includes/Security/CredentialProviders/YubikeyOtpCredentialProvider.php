<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\HttpHelper;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

class YubikeyOtpCredentialProvider extends CredentialProviderBase
{
    /** @var HttpHelper */
    private $httpHelper;
    /**
     * @var SiteConfiguration
     */
    private $configuration;

    public function __construct(PdoDatabase $database, SiteConfiguration $configuration, HttpHelper $httpHelper)
    {
        parent::__construct($database, $configuration, 'yubikeyotp');
        $this->httpHelper = $httpHelper;
        $this->configuration = $configuration;
    }

    public function authenticate(User $user, $data)
    {
        if (is_array($data)) {
            return false;
        }

        $credentialData = $this->getCredentialData($user->getId());

        if ($credentialData === null) {
            return false;
        }

        if ($credentialData->getData() !== $this->getYubikeyId($data)) {
            // different device
            return false;
        }

        return $this->verifyToken($data);
    }

    public function setCredential(User $user, $factor, $data)
    {
        $keyId = $this->getYubikeyId($data);
        $valid = $this->verifyToken($data);

        if (!$valid) {
            throw new ApplicationLogicException("Provided token is not valid.");
        }

        $storedData = $this->getCredentialData($user->getId());

        if ($storedData === null) {
            $storedData = $this->createNewCredential($user);
        }

        $storedData->setData($keyId);
        $storedData->setFactor($factor);
        $storedData->setVersion(1);
        $storedData->setPriority(8);

        $storedData->save();
    }

    /**
     * Get the Yubikey ID.
     *
     * This looks like it's just dumping the "password" that's stored in the database, but it's actually fine.
     *
     * We only store the "serial number" of the Yubikey - if we get a validated (by webservice) token prefixed with the
     * serial number, that's a successful OTP authentication. Thus, retrieving the stored data is just retrieving the
     * yubikey's serial number (in modhex format), since the actual security credentials are stored on the device.
     *
     * Note that the serial number is actually the credential serial number - it's possible to regenerate the keys on
     * the device, and that will change the serial number too.
     *
     * More information about the structure of OTPs can be found here:
     * https://developers.yubico.com/OTP/OTPs_Explained.html
     *
     * @param int $userId
     *
     * @return null|string
     */
    public function getYubikeyData($userId)
    {
        $credential = $this->getCredentialData($userId);

        if ($credential === null) {
            return null;
        }

        return $credential->getData();
    }

    /**
     * @param $result
     *
     * @return array
     */
    private function parseYubicoApiResult($result)
    {
        $data = array();
        foreach (explode("\r\n", $result) as $line) {
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $data[substr($line, 0, $pos)] = substr($line, $pos + 1);
        }

        return $data;
    }

    private function getYubikeyId($data)
    {
        return substr($data, 0, -32);
    }

    private function verifyHmac($apiResponse, $apiKey)
    {
        ksort($apiResponse);
        $signature = $apiResponse['h'];
        unset($apiResponse['h']);

        $data = array();
        foreach ($apiResponse as $key => $value) {
            $data[] = $key . "=" . $value;
        }
        $dataString = implode('&', $data);

        $hmac = base64_encode(hash_hmac('sha1', $dataString, base64_decode($apiKey), true));

        return $hmac === $signature;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function verifyToken($data)
    {
        $result = $this->httpHelper->get('https://api.yubico.com/wsapi/2.0/verify', array(
            'id'    => $this->configuration->getYubicoApiId(),
            'otp'   => $data,
            'nonce' => md5(openssl_random_pseudo_bytes(64)),
        ));

        $apiResponse = $this->parseYubicoApiResult($result);

        if (!$this->verifyHmac($apiResponse, $this->configuration->getYubicoApiKey())) {
            return false;
        }

        return $apiResponse['status'] == 'OK';
    }
}

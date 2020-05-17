<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\SiteConfiguration;

class EncryptionHelper
{
    /**
     * @var SiteConfiguration
     */
    private $configuration;

    /**
     * EncryptionHelper constructor.
     *
     * @param SiteConfiguration $configuration
     */
    public function __construct(SiteConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function encryptData($secret)
    {
        $iv = openssl_random_pseudo_bytes(16);
        $password = $this->getEncryptionKey();
        $encryptedKey = openssl_encrypt($secret, 'aes-256-ctr', $password, OPENSSL_RAW_DATA, $iv);

        $data = base64_encode($iv) . '|' . base64_encode($encryptedKey);

        return $data;
    }

    public function decryptData($data)
    {
        list($iv, $encryptedKey) = array_map('base64_decode', explode('|', $data));

        $password = $this->getEncryptionKey();

        $secret = openssl_decrypt($encryptedKey, 'aes-256-ctr', $password, OPENSSL_RAW_DATA, $iv);

        return $secret;
    }

    /**
     * @return string
     */
    private function getEncryptionKey()
    {
        return openssl_digest($this->configuration->getTotpEncryptionKey(), 'sha256');
    }
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use PDO;
use Waca\DataObjects\User;
use Waca\Helpers\HttpHelper;
use Waca\PdoDatabase;
use Waca\Security\CredentialProviders\ICredentialProvider;
use Waca\Security\CredentialProviders\PasswordCredentialProvider;
use Waca\Security\CredentialProviders\ScratchTokenCredentialProvider;
use Waca\Security\CredentialProviders\TotpCredentialProvider;
use Waca\Security\CredentialProviders\WebAuthnCredentialProvider;
use Waca\Security\CredentialProviders\YubikeyOtpCredentialProvider;
use Waca\SiteConfiguration;

class AuthenticationManager
{
    const AUTH_OK = 1;
    const AUTH_FAIL = 2;
    const AUTH_REQUIRE_NEXT_STAGE = 3;
    private $typeMap = array();
    /**
     * @var PdoDatabase
     */
    private $database;

    /**
     * AuthenticationManager constructor.
     *
     * @param PdoDatabase       $database
     * @param SiteConfiguration $siteConfiguration
     * @param HttpHelper        $httpHelper
     */
    public function __construct(PdoDatabase $database, SiteConfiguration $siteConfiguration, HttpHelper $httpHelper)
    {
        // setup providers
        // note on type map: this *must* be the value in the database, as this is what it maps.
        $this->typeMap['password'] = new PasswordCredentialProvider($database, $siteConfiguration);
        $this->typeMap['yubikeyotp'] = new YubikeyOtpCredentialProvider($database, $siteConfiguration, $httpHelper);
        $this->typeMap['totp'] = new TotpCredentialProvider($database, $siteConfiguration);
        $this->typeMap['scratch'] = new ScratchTokenCredentialProvider($database, $siteConfiguration);
        $this->typeMap['webauthn'] = new WebAuthnCredentialProvider($database, $siteConfiguration);
        $this->database = $database;
    }

    public function authenticate(User $user, $data, $stage)
    {
        $sql = 'SELECT type FROM credential WHERE user = :user AND factor = :stage AND disabled = 0 ORDER BY priority ASC';
        $statement = $this->database->prepare($sql);
        $statement->execute(array(':user' => $user->getId(), ':stage' => $stage));
        $options = $statement->fetchAll(PDO::FETCH_COLUMN);

        $sql = 'SELECT count(DISTINCT factor) FROM credential WHERE user = :user AND factor > :stage AND disabled = 0 AND type <> :scratch';
        $statement = $this->database->prepare($sql);
        $statement->execute(array(':user' => $user->getId(), ':stage' => $stage, ':scratch' => 'scratch'));
        $requiredFactors = $statement->fetchColumn();

        // prep the correct OK response based on how many factors are ahead of this one
        $success = self::AUTH_OK;
        if ($requiredFactors > 0) {
            $success = self::AUTH_REQUIRE_NEXT_STAGE;
        }

        foreach ($options as $type) {
            if (!isset($this->typeMap[$type])) {
                // does this type have a credentialProvider registered?
                continue;
            }

            /** @var ICredentialProvider $credentialProvider */
            $credentialProvider = $this->typeMap[$type];
            if ($credentialProvider->authenticate($user, $data)) {
                return $success;
            }
        }

        // We've iterated over all the available providers for this stage.
        // They all hate you.
        return self::AUTH_FAIL;
    }
}
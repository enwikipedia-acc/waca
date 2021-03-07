<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use DateTimeImmutable;
use MediaWiki\OAuthClient\Exception;
use PDOStatement;
use Waca\DataObjects\OAuthIdentity;
use Waca\DataObjects\OAuthToken;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\CurlException;
use Waca\Exceptions\OAuthException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Helpers\Interfaces\IMediaWikiClient;
use Waca\Helpers\Interfaces\IOAuthProtocolHelper;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

class OAuthUserHelper implements IMediaWikiClient
{
    const TOKEN_REQUEST = 'request';
    const TOKEN_ACCESS = 'access';
    /** @var PDOStatement */
    private static $tokenCountStatement = null;
    /** @var PDOStatement */
    private $getTokenStatement;
    /**
     * @var User
     */
    private $user;
    /**
     * @var PdoDatabase
     */
    private $database;
    /**
     * @var IOAuthProtocolHelper
     */
    private $oauthProtocolHelper;
    /**
     * @var bool|null Is the user linked to OAuth
     */
    private $linked;
    private $partiallyLinked;
    /** @var OAuthToken */
    private $accessToken;
    /** @var bool */
    private $accessTokenLoaded = false;
    /**
     * @var OAuthIdentity
     */
    private $identity = null;
    /**
     * @var bool
     */
    private $identityLoaded = false;
    /**
     * @var SiteConfiguration
     */
    private $siteConfiguration;

    private $legacyTokens;

    #region Static methods

    public static function findUserByRequestToken($requestToken, PdoDatabase $database)
    {
        $statement = $database->prepare(<<<'SQL'
            SELECT u.* FROM user u 
            INNER JOIN oauthtoken t ON t.user = u.id 
            WHERE t.type = :type AND t.token = :token
SQL
        );
        $statement->execute(array(':type' => self::TOKEN_REQUEST, ':token' => $requestToken));

        /** @var User $user */
        $user = $statement->fetchObject(User::class);
        $statement->closeCursor();

        if ($user === false) {
            throw new ApplicationLogicException('Token not found in store, please try again');
        }

        $user->setDatabase($database);

        return $user;
    }

    public static function userIsFullyLinked(User $user, PdoDatabase $database = null)
    {
        if (self::$tokenCountStatement === null && $database === null) {
            throw new ApplicationLogicException('Static link request without initialised statement');
        }

        return self::runTokenCount($user->getId(), $database, self::TOKEN_ACCESS);
    }

    public static function userIsPartiallyLinked(User $user, PdoDatabase $database = null)
    {
        if (self::$tokenCountStatement === null && $database === null) {
            throw new ApplicationLogicException('Static link request without initialised statement');
        }

        if (self::userIsFullyLinked($user, $database)) {
            return false;
        }

        return self::runTokenCount($user->getId(), $database, self::TOKEN_REQUEST)
            || $user->getOnWikiName() == null;
    }

    /**
     * @param PdoDatabase $database
     */
    public static function prepareTokenCountStatement(PdoDatabase $database)
    {
        if (self::$tokenCountStatement === null) {
            self::$tokenCountStatement = $database->prepare('SELECT COUNT(*) FROM oauthtoken WHERE user = :user AND type = :type');
        }
    }

    private static function runTokenCount($userId, $database, $tokenType)
    {
        if (self::$tokenCountStatement === null) {
            self::prepareTokenCountStatement($database);
        }

        self::$tokenCountStatement->execute(array(
            ':user' => $userId,
            ':type' => $tokenType,
        ));

        $tokenCount = self::$tokenCountStatement->fetchColumn();
        $linked = $tokenCount > 0;
        self::$tokenCountStatement->closeCursor();

        return $linked;
    }

    #endregion Static methods

    /**
     * OAuthUserHelper constructor.
     *
     * @param User                 $user
     * @param PdoDatabase          $database
     * @param IOAuthProtocolHelper $oauthProtocolHelper
     * @param SiteConfiguration    $siteConfiguration
     */
    public function __construct(
        User $user,
        PdoDatabase $database,
        IOAuthProtocolHelper $oauthProtocolHelper,
        SiteConfiguration $siteConfiguration
    ) {
        $this->user = $user;
        $this->database = $database;
        $this->oauthProtocolHelper = $oauthProtocolHelper;

        $this->linked = null;
        $this->partiallyLinked = null;
        $this->siteConfiguration = $siteConfiguration;

        self::prepareTokenCountStatement($database);
        $this->getTokenStatement = $this->database->prepare('SELECT * FROM oauthtoken WHERE user = :user AND type = :type');

        $this->legacyTokens = $this->siteConfiguration->getOauthLegacyConsumerTokens();
    }

    /**
     * Determines if the user is fully connected to OAuth.
     *
     * @return bool
     */
    public function isFullyLinked()
    {
        if ($this->linked === null) {
            $this->linked = self::userIsFullyLinked($this->user, $this->database);
        }

        return $this->linked;
    }

    /**
     * Attempts to figure out if a user is partially linked to OAuth, and therefore needs to complete the OAuth
     * procedure before configuring.
     * @return bool
     */
    public function isPartiallyLinked()
    {
        if ($this->partiallyLinked === null) {
            $this->partiallyLinked = self::userIsPartiallyLinked($this->user, $this->database);
        }

        return $this->partiallyLinked;
    }

    public function canCreateAccount()
    {
        return $this->isFullyLinked()
            && $this->getIdentity(true)->getGrantBasic()
            && $this->getIdentity(true)->getGrantHighVolume()
            && $this->getIdentity(true)->getGrantCreateAccount();
    }

    public function canWelcome()
    {
        return $this->isFullyLinked()
            && $this->getIdentity(true)->getGrantBasic()
            && $this->getIdentity(true)->getGrantHighVolume()
            && $this->getIdentity(true)->getGrantCreateEditMovePage();
    }

    /**
     * @throws OAuthException
     * @throws CurlException
     * @throws OptimisticLockFailedException
     * @throws Exception
     */
    public function refreshIdentity()
    {
        $this->loadIdentity();

        if ($this->identity === null) {
            $this->identity = new OAuthIdentity();
            $this->identity->setUserId($this->user->getId());
            $this->identity->setDatabase($this->database);
        }

        $token = $this->loadAccessToken();

        try {
            $rawTicket = $this->oauthProtocolHelper->getIdentityTicket($token->getToken(), $token->getSecret());
        }
        catch (Exception $ex) {
            if (strpos($ex->getMessage(), "mwoauthdatastore-access-token-not-found") !== false) {
                throw new OAuthException('No approved grants for this access token.', -1, $ex);
            }

            throw $ex;
        }

        $this->identity->populate($rawTicket);

        if (!$this->identityIsValid()) {
            throw new OAuthException('Identity ticket is not valid!');
        }

        $this->identity->save();

        $this->user->setOnWikiName($this->identity->getUsername());
        $this->user->save();
    }

    /**
     * @return string
     * @throws CurlException
     */
    public function getRequestToken()
    {
        $token = $this->oauthProtocolHelper->getRequestToken();

        $this->partiallyLinked = true;
        $this->linked = false;

        $this->database
            ->prepare('DELETE FROM oauthtoken WHERE user = :user AND type = :type')
            ->execute(array(':user' => $this->user->getId(), ':type' => self::TOKEN_REQUEST));

        $this->database
            ->prepare('INSERT INTO oauthtoken (user, type, token, secret, expiry) VALUES (:user, :type, :token, :secret, DATE_ADD(NOW(), INTERVAL 1 DAY))')
            ->execute(array(
                ':user'   => $this->user->getId(),
                ':type'   => self::TOKEN_REQUEST,
                ':token'  => $token->key,
                ':secret' => $token->secret,
            ));

        return $this->oauthProtocolHelper->getAuthoriseUrl($token->key);
    }

    /**
     * @param $verificationToken
     *
     * @throws ApplicationLogicException
     * @throws CurlException
     * @throws OAuthException
     * @throws OptimisticLockFailedException
     */
    public function completeHandshake($verificationToken)
    {
        $this->getTokenStatement->execute(array(':user' => $this->user->getId(), ':type' => self::TOKEN_REQUEST));

        /** @var OAuthToken $token */
        $token = $this->getTokenStatement->fetchObject(OAuthToken::class);
        $this->getTokenStatement->closeCursor();

        if ($token === false) {
            throw new ApplicationLogicException('Cannot find request token');
        }

        $token->setDatabase($this->database);

        $accessToken = $this->oauthProtocolHelper->callbackCompleted($token->getToken(), $token->getSecret(),
            $verificationToken);

        $clearStatement = $this->database->prepare('DELETE FROM oauthtoken WHERE user = :u AND type = :t');
        $clearStatement->execute(array(':u' => $this->user->getId(), ':t' => self::TOKEN_ACCESS));

        $token->setToken($accessToken->key);
        $token->setSecret($accessToken->secret);
        $token->setType(self::TOKEN_ACCESS);
        $token->setExpiry(null);
        $token->save();

        $this->partiallyLinked = false;
        $this->linked = true;

        $this->refreshIdentity();
    }

    public function detach()
    {
        $this->loadIdentity();

        $this->identity->delete();
        $statement = $this->database->prepare('DELETE FROM oauthtoken WHERE user = :user');
        $statement->execute(array(':user' => $this->user->getId()));

        $this->identity = null;
        $this->linked = false;
        $this->partiallyLinked = false;
    }

    /**
     * @param bool $expiredOk
     *
     * @return OAuthIdentity
     * @throws OAuthException
     */
    public function getIdentity($expiredOk = false)
    {
        $this->loadIdentity();

        if (!$this->identityIsValid($expiredOk)) {
            throw new OAuthException('Stored identity is not valid.');
        }

        return $this->identity;
    }

    public function doApiCall($params, $method)
    {
        // Ensure we're logged in
        $params['assert'] = 'user';

        $token = $this->loadAccessToken();
        return $this->oauthProtocolHelper->apiCall($params, $token->getToken(), $token->getSecret(), $method);
    }

    /**
     * @param bool $expiredOk
     *
     * @return bool
     */
    private function identityIsValid($expiredOk = false)
    {
        $this->loadIdentity();

        if ($this->identity === null) {
            return false;
        }

        if ($this->identity->getIssuedAtTime() === false
            || $this->identity->getExpirationTime() === false
            || $this->identity->getAudience() === false
            || $this->identity->getIssuer() === false
        ) {
            // this isn't populated properly.
            return false;
        }

        $issue = DateTimeImmutable::createFromFormat("U", $this->identity->getIssuedAtTime());
        $now = new DateTimeImmutable();

        if ($issue > $now) {
            // wat.
            return false;
        }

        if ($this->identityExpired() && !$expiredOk) {
            // soz.
            return false;
        }

        if ($this->identity->getAudience() !== $this->siteConfiguration->getOAuthConsumerToken()) {
            // token not issued for us

            // we allow cases where the cache is expired and the cache is for a legacy token
            if (!($expiredOk && in_array($this->identity->getAudience(), $this->legacyTokens))) {
                return false;
            }
        }

        if ($this->identity->getIssuer() !== $this->siteConfiguration->getOauthMediaWikiCanonicalServer()) {
            // token not issued by the right person
            return false;
        }

        // can't find a reason to not trust it
        return true;
    }

    /**
     * @return bool
     */
    public function identityExpired()
    {
        // allowed max age
        $gracePeriod = $this->siteConfiguration->getOauthIdentityGraceTime();

        $expiry = DateTimeImmutable::createFromFormat("U", $this->identity->getExpirationTime());
        $graceExpiry = $expiry->modify($gracePeriod);
        $now = new DateTimeImmutable();

        return $graceExpiry < $now;
    }

    /**
     * Loads the OAuth identity from the database for the current user.
     */
    private function loadIdentity()
    {
        if ($this->identityLoaded) {
            return;
        }

        $statement = $this->database->prepare('SELECT * FROM oauthidentity WHERE user = :user');
        $statement->execute(array(':user' => $this->user->getId()));
        /** @var OAuthIdentity $obj */
        $obj = $statement->fetchObject(OAuthIdentity::class);

        if ($obj === false) {
            // failed to load identity.
            $this->identityLoaded = true;
            $this->identity = null;

            return;
        }

        $obj->setDatabase($this->database);
        $this->identityLoaded = true;
        $this->identity = $obj;
    }

    /**
     * @return OAuthToken
     * @throws OAuthException
     */
    private function loadAccessToken()
    {
        if (!$this->accessTokenLoaded) {
            $this->getTokenStatement->execute(array(':user' => $this->user->getId(), ':type' => self::TOKEN_ACCESS));
            /** @var OAuthToken $token */
            $token = $this->getTokenStatement->fetchObject(OAuthToken::class);
            $this->getTokenStatement->closeCursor();

            if ($token === false) {
                throw new OAuthException('Access token not found!');
            }

            $this->accessToken = $token;
            $this->accessTokenLoaded = true;
        }

        return $this->accessToken;
    }
}

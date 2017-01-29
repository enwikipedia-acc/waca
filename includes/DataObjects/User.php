<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use DateTime;
use Exception;
use JWT;
use PDO;
use UnexpectedValueException;
use Waca\AuthUtility;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Helpers\Interfaces\IOAuthHelper;
use Waca\IdentificationVerifier;
use Waca\PdoDatabase;
use Waca\SessionAlert;
use Waca\WebRequest;

/**
 * User data object
 */
class User extends DataObject
{
    const STATUS_ACTIVE = 'Active';
    const STATUS_SUSPENDED = 'Suspended';
    const STATUS_DECLINED = 'Declined';
    const STATUS_NEW = 'New';
    private $username;
    private $email;
    private $password;
    private $status = self::STATUS_NEW;
    private $onwikiname = "##OAUTH##";
    private $welcome_sig = "";
    private $lastactive = "0000-00-00 00:00:00";
    private $forcelogout = 0;
    private $forceidentified = null;
    private $welcome_template = 0;
    private $abortpref = 0;
    private $confirmationdiff = 0;
    private $emailsig = "";
    /** @var null|string */
    private $oauthrequesttoken = null;
    /** @var null|string */
    private $oauthrequestsecret = null;
    /** @var null|string */
    private $oauthaccesstoken = null;
    /** @var null|string */
    private $oauthaccesssecret = null;
    private $oauthidentitycache = null;
    /** @var User Cache variable of the current user - it's never going to change in the middle of a request. */
    private static $currentUser;
    /** @var null|JWT The identity cache */
    private $identityCache = null;
    #region Object load methods

    /**
     * Gets the currently logged in user
     *
     * @param PdoDatabase $database
     *
     * @return User|CommunityUser
     */
    public static function getCurrent(PdoDatabase $database)
    {
        if (self::$currentUser === null) {
            $sessionId = WebRequest::getSessionUserId();

            if ($sessionId !== null) {
                /** @var User $user */
                $user = self::getById($sessionId, $database);

                if ($user === false) {
                    self::$currentUser = new CommunityUser();
                }
                else {
                    self::$currentUser = $user;
                }
            }
            else {
                $anonymousCoward = new CommunityUser();

                self::$currentUser = $anonymousCoward;
            }
        }

        return self::$currentUser;
    }

    /**
     * Gets a user by their user ID
     *
     * Pass -1 to get the community user.
     *
     * @param int|null    $id
     * @param PdoDatabase $database
     *
     * @return User|false
     */
    public static function getById($id, PdoDatabase $database)
    {
        if ($id === null || $id == -1) {
            return new CommunityUser();
        }

        /** @var User|false $user */
        $user = parent::getById($id, $database);

        return $user;
    }

    /**
     * @return CommunityUser
     */
    public static function getCommunity()
    {
        return new CommunityUser();
    }

    /**
     * Gets a user by their username
     *
     * @param  string      $username
     * @param  PdoDatabase $database
     *
     * @return CommunityUser|User|false
     */
    public static function getByUsername($username, PdoDatabase $database)
    {
        global $communityUsername;
        if ($username == $communityUsername) {
            return new CommunityUser();
        }

        $statement = $database->prepare("SELECT * FROM user WHERE username = :id LIMIT 1;");
        $statement->bindValue(":id", $username);

        $statement->execute();

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject != false) {
            $resultObject->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * Gets a user by their on-wiki username.
     *
     * Don't use without asking me first. It's really inefficient in it's current implementation.
     * We need to restructure the user table again to make this more efficient.
     * We don't actually store the on-wiki name in the table any more, instead we
     * are storing JSON in a column (!!). Yep, my fault. Code review is an awesome thing.
     *            -- stw 2015-10-20
     *
     * @param string      $username
     * @param PdoDatabase $database
     *
     * @return User|false
     */
    public static function getByOnWikiUsername($username, PdoDatabase $database)
    {
        // Firstly, try to search by the efficient database lookup.
        $statement = $database->prepare("SELECT * FROM user WHERE onwikiname = :id LIMIT 1;");
        $statement->bindValue(":id", $username);
        $statement->execute();

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject != false) {
            $resultObject->setDatabase($database);

            return $resultObject;
        }

        // For active users, the above has failed. Let's do it the hard way.
        $sqlStatement = "SELECT * FROM user WHERE onwikiname = '##OAUTH##' AND oauthaccesstoken IS NOT NULL;";
        $statement = $database->prepare($sqlStatement);
        $statement->execute();
        $resultSet = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        /** @var User $user */
        foreach ($resultSet as $user) {
            // We have to set this before doing OAuth queries. :(
            $user->setDatabase($database);

            // Using cached data here!
            if ($user->getOAuthOnWikiName(true) == $username) {
                // Success.
                return $user;
            }
        }

        // Cached data failed. Let's do it the *REALLY* hard way.
        foreach ($resultSet as $user) {
            // We have to set this before doing OAuth queries. :(
            $user->setDatabase($database);

            // Don't use the cached data, but instead query the API.
            if ($user->getOAuthOnWikiName(false) == $username) {
                // Success.
                return $user;
            }
        }

        // Nope. Sorry.
        return false;
    }

    /**
     * Gets a user by their OAuth request token
     *
     * @param string      $requestToken
     * @param PdoDatabase $database
     *
     * @return User|false
     */
    public static function getByRequestToken($requestToken, PdoDatabase $database)
    {
        $statement = $database->prepare("SELECT * FROM user WHERE oauthrequesttoken = :id LIMIT 1;");
        $statement->bindValue(":id", $requestToken);

        $statement->execute();

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject != false) {
            $resultObject->setDatabase($database);
        }

        return $resultObject;
    }

    #endregion

    /**
     * Saves the current object
     *
     * @throws Exception
     */
    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
				INSERT INTO `user` ( 
					username, email, password, status, onwikiname, welcome_sig, 
					lastactive, forcelogout, forceidentified,
					welcome_template, abortpref, confirmationdiff, emailsig, 
					oauthrequesttoken, oauthrequestsecret, 
					oauthaccesstoken, oauthaccesssecret
				) VALUES (
					:username, :email, :password, :status, :onwikiname, :welcome_sig,
					:lastactive, :forcelogout, :forceidentified,
					:welcome_template, :abortpref, :confirmationdiff, :emailsig, 
					:ort, :ors, :oat, :oas
				);
SQL
            );
            $statement->bindValue(":username", $this->username);
            $statement->bindValue(":email", $this->email);
            $statement->bindValue(":password", $this->password);
            $statement->bindValue(":status", $this->status);
            $statement->bindValue(":onwikiname", $this->onwikiname);
            $statement->bindValue(":welcome_sig", $this->welcome_sig);
            $statement->bindValue(":lastactive", $this->lastactive);
            $statement->bindValue(":forcelogout", $this->forcelogout);
            $statement->bindValue(":forceidentified", $this->forceidentified);
            $statement->bindValue(":welcome_template", $this->welcome_template);
            $statement->bindValue(":abortpref", $this->abortpref);
            $statement->bindValue(":confirmationdiff", $this->confirmationdiff);
            $statement->bindValue(":emailsig", $this->emailsig);
            $statement->bindValue(":ort", $this->oauthrequesttoken);
            $statement->bindValue(":ors", $this->oauthrequestsecret);
            $statement->bindValue(":oat", $this->oauthaccesstoken);
            $statement->bindValue(":oas", $this->oauthaccesssecret);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            // update
            $statement = $this->dbObject->prepare(<<<SQL
				UPDATE `user` SET 
					username = :username, email = :email, 
					password = :password, status = :status,
					onwikiname = :onwikiname, welcome_sig = :welcome_sig, 
					lastactive = :lastactive, forcelogout = :forcelogout, 
					forceidentified = :forceidentified,
					welcome_template = :welcome_template, abortpref = :abortpref, 
					confirmationdiff = :confirmationdiff, emailsig = :emailsig, 
					oauthrequesttoken = :ort, oauthrequestsecret = :ors, 
					oauthaccesstoken = :oat, oauthaccesssecret = :oas,
					updateversion = updateversion + 1
				WHERE id = :id AND updateversion = :updateversion
				LIMIT 1;
SQL
            );
            $statement->bindValue(":forceidentified", $this->forceidentified);

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(':username', $this->username);
            $statement->bindValue(':email', $this->email);
            $statement->bindValue(':password', $this->password);
            $statement->bindValue(':status', $this->status);
            $statement->bindValue(':onwikiname', $this->onwikiname);
            $statement->bindValue(':welcome_sig', $this->welcome_sig);
            $statement->bindValue(':lastactive', $this->lastactive);
            $statement->bindValue(':forcelogout', $this->forcelogout);
            $statement->bindValue(':forceidentified', $this->forceidentified);
            $statement->bindValue(':welcome_template', $this->welcome_template);
            $statement->bindValue(':abortpref', $this->abortpref);
            $statement->bindValue(':confirmationdiff', $this->confirmationdiff);
            $statement->bindValue(':emailsig', $this->emailsig);
            $statement->bindValue(':ort', $this->oauthrequesttoken);
            $statement->bindValue(':ors', $this->oauthrequestsecret);
            $statement->bindValue(':oat', $this->oauthaccesstoken);
            $statement->bindValue(':oas', $this->oauthaccesssecret);

            if (!$statement->execute()) {
                throw new Exception($statement->errorInfo());
            }

            if ($statement->rowCount() !== 1) {
                throw new OptimisticLockFailedException();
            }

            $this->updateversion++;
        }
    }

    /**
     * Authenticates the user with the supplied password
     *
     * @param string $password
     *
     * @return bool
     * @throws Exception
     * @category Security-Critical
     */
    public function authenticate($password)
    {
        $result = AuthUtility::testCredentials($password, $this->password);

        if ($result === true) {
            // password version is out of date, update it.
            if (!AuthUtility::isCredentialVersionLatest($this->password)) {
                $this->password = AuthUtility::encryptPassword($password);
                $this->save();
            }
        }

        return $result;
    }

    #region properties

    /**
     * Gets the tool username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the tool username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;

        // If this isn't a brand new user, then it's a rename, force the logout
        if (!$this->isNew()) {
            $this->forcelogout = 1;
        }
    }

    /**
     * Gets the user's email address
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the user's email address
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Sets the user's password
     *
     * @param string $password the plaintext password
     *
     * @category Security-Critical
     */
    public function setPassword($password)
    {
        $this->password = AuthUtility::encryptPassword($password);
    }

    /**
     * Gets the status (User, Admin, Suspended, etc - excludes checkuser) of the user.
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Gets the user's on-wiki name
     * @return string
     */
    public function getOnWikiName()
    {
        if ($this->oauthaccesstoken !== null) {
            try {
                return $this->getOAuthOnWikiName();
            }
            catch (Exception $ex) {
                // urm.. log this?
                return $this->onwikiname;
            }
        }

        return $this->onwikiname;
    }

    /**
     * This is probably NOT the function you want!
     *
     * Take a look at getOnWikiName() instead.
     * @return string
     */
    public function getStoredOnWikiName()
    {
        return $this->onwikiname;
    }

    /**
     * Sets the user's on-wiki name
     *
     * This can have interesting side-effects with OAuth.
     *
     * @param string $onWikiName
     */
    public function setOnWikiName($onWikiName)
    {
        $this->onwikiname = $onWikiName;
    }

    /**
     * Gets the welcome signature
     * @return string
     */
    public function getWelcomeSig()
    {
        return $this->welcome_sig;
    }

    /**
     * Sets the welcome signature
     *
     * @param string $welcomeSig
     */
    public function setWelcomeSig($welcomeSig)
    {
        $this->welcome_sig = $welcomeSig;
    }

    /**
     * Gets the last activity date for the user
     *
     * @return string
     * @todo This should probably return an instance of DateTime
     */
    public function getLastActive()
    {
        return $this->lastactive;
    }

    /**
     * Gets the user's forced logout status
     *
     * @return bool
     */
    public function getForceLogout()
    {
        return $this->forcelogout == 1;
    }

    /**
     * Sets the user's forced logout status
     *
     * @param bool $forceLogout
     */
    public function setForceLogout($forceLogout)
    {
        $this->forcelogout = $forceLogout ? 1 : 0;
    }

    /**
     * Returns the ID of the welcome template used.
     * @return int
     */
    public function getWelcomeTemplate()
    {
        return $this->welcome_template;
    }

    /**
     * Sets the ID of the welcome template used.
     *
     * @param int $welcomeTemplate
     */
    public function setWelcomeTemplate($welcomeTemplate)
    {
        $this->welcome_template = $welcomeTemplate;
    }

    /**
     * Gets the user's abort preference
     * @todo this is badly named too! Also a bool that's actually an int.
     * @return int
     */
    public function getAbortPref()
    {
        return $this->abortpref;
    }

    /**
     * Sets the user's abort preference
     * @todo rename, retype, and re-comment.
     *
     * @param int $abortPreference
     */
    public function setAbortPref($abortPreference)
    {
        $this->abortpref = $abortPreference;
    }

    /**
     * Gets the user's confirmation diff. Unused if OAuth is in use.
     * @return int the diff ID
     */
    public function getConfirmationDiff()
    {
        return $this->confirmationdiff;
    }

    /**
     * Sets the user's confirmation diff.
     *
     * @param int $confirmationDiff
     */
    public function setConfirmationDiff($confirmationDiff)
    {
        $this->confirmationdiff = $confirmationDiff;
    }

    /**
     * Gets the users' email signature used on outbound mail.
     * @todo rename me!
     * @return string
     */
    public function getEmailSig()
    {
        return $this->emailsig;
    }

    /**
     * Sets the user's email signature for outbound mail.
     *
     * @param string $emailSignature
     */
    public function setEmailSig($emailSignature)
    {
        $this->emailsig = $emailSignature;
    }

    /**
     * Gets the user's OAuth request token.
     *
     * @todo move me to a collaborator.
     * @return null|string
     */
    public function getOAuthRequestToken()
    {
        return $this->oauthrequesttoken;
    }

    /**
     * Sets the user's OAuth request token
     * @todo move me to a collaborator
     *
     * @param string $oAuthRequestToken
     */
    public function setOAuthRequestToken($oAuthRequestToken)
    {
        $this->oauthrequesttoken = $oAuthRequestToken;
    }

    /**
     * Gets the users OAuth request secret
     * @category Security-Critical
     * @todo     move me to a collaborator
     * @return null|string
     */
    public function getOAuthRequestSecret()
    {
        return $this->oauthrequestsecret;
    }

    /**
     * Sets the user's OAuth request secret
     * @todo move me to a collaborator
     *
     * @param string $oAuthRequestSecret
     */
    public function setOAuthRequestSecret($oAuthRequestSecret)
    {
        $this->oauthrequestsecret = $oAuthRequestSecret;
    }

    /**
     * Gets the user's access token
     * @category Security-Critical
     * @todo     move me to a collaborator
     * @return null|string
     */
    public function getOAuthAccessToken()
    {
        return $this->oauthaccesstoken;
    }

    /**
     * Sets the user's access token
     * @todo move me to a collaborator
     *
     * @param string $oAuthAccessToken
     */
    public function setOAuthAccessToken($oAuthAccessToken)
    {
        $this->oauthaccesstoken = $oAuthAccessToken;
    }

    /**
     * Gets the user's OAuth access secret
     * @category Security-Critical
     * @todo     move me to a collaborator
     * @return null|string
     */
    public function getOAuthAccessSecret()
    {
        return $this->oauthaccesssecret;
    }

    /**
     * Sets the user's OAuth access secret
     * @todo move me to a collaborator
     *
     * @param string $oAuthAccessSecret
     */
    public function setOAuthAccessSecret($oAuthAccessSecret)
    {
        $this->oauthaccesssecret = $oAuthAccessSecret;
    }

    #endregion

    #region user access checks

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Tests if the user is identified
     *
     * @param IdentificationVerifier $iv
     *
     * @return bool
     * @todo     Figure out what on earth is going on with PDO's typecasting here.  Apparently, it returns string("0") for
     *       the force-unidentified case, and int(1) for the identified case?!  This is quite ugly, but probably needed
     *       to play it safe for now.
     * @category Security-Critical
     */
    public function isIdentified(IdentificationVerifier $iv)
    {
        if ($this->forceidentified === 0 || $this->forceidentified === "0") {
            // User forced to unidentified in the database.
            return false;
        }
        elseif ($this->forceidentified === 1 || $this->forceidentified === "1") {
            // User forced to identified in the database.
            return true;
        }
        else {
            // User not forced to any particular identified status; consult IdentificationVerifier
            return $iv->isUserIdentified($this->getOnWikiName());
        }
    }

    /**
     * Tests if the user is suspended
     * @return bool
     * @category Security-Critical
     */
    public function isSuspended()
    {
        return $this->status == self::STATUS_SUSPENDED;
    }

    /**
     * Tests if the user is new
     * @return bool
     * @category Security-Critical
     */
    public function isNewUser()
    {
        return $this->status == self::STATUS_NEW;
    }

    /**
     * Tests if the user has been declined access to the tool
     * @return bool
     * @category Security-Critical
     */
    public function isDeclined()
    {
        return $this->status == self::STATUS_DECLINED;
    }

    /**
     * Tests if the user is the community user
     *
     * @todo     decide if this means logged out. I think it usually does.
     * @return bool
     * @category Security-Critical
     */
    public function isCommunityUser()
    {
        return false;
    }

    #endregion 

    #region OAuth

    /**
     * @todo     move me to a collaborator
     *
     * @param bool $useCached
     *
     * @return mixed|null
     * @category Security-Critical
     */
    public function getOAuthIdentity($useCached = false)
    {
        if ($this->oauthaccesstoken === null) {
            $this->clearOAuthData();
        }

        global $oauthConsumerToken, $oauthMediaWikiCanonicalServer;

        if ($this->oauthidentitycache == null) {
            $this->identityCache = null;
        }
        else {
            $this->identityCache = unserialize($this->oauthidentitycache);
        }

        // check the cache
        if (
            $this->identityCache != null &&
            $this->identityCache->aud == $oauthConsumerToken &&
            $this->identityCache->iss == $oauthMediaWikiCanonicalServer
        ) {
            if (
                $useCached || (
                    DateTime::createFromFormat("U", $this->identityCache->iat) < new DateTime() &&
                    DateTime::createFromFormat("U", $this->identityCache->exp) > new DateTime()
                )
            ) {
                // Use cached value - it's either valid or we don't care.
                return $this->identityCache;
            }
            else {
                // Cache expired and not forcing use of cached value
                $this->getIdentityCache();

                return $this->identityCache;
            }
        }
        else {
            // Cache isn't ours or doesn't exist
            $this->getIdentityCache();

            return $this->identityCache;
        }
    }

    /**
     * @todo     move me to a collaborator
     *
     * @param mixed $useCached Set to false for everything where up-to-date data is important.
     *
     * @return mixed
     * @category Security-Critical
     */
    private function getOAuthOnWikiName($useCached = false)
    {
        $identity = $this->getOAuthIdentity($useCached);
        if ($identity !== null) {
            return $identity->username;
        }

        return false;
    }

    /**
     * @return bool
     * @todo move me to a collaborator
     */
    public function isOAuthLinked()
    {
        if ($this->onwikiname === "##OAUTH##") {
            return true; // special value. If an account must be oauth linked, this is true.
        }

        return $this->oauthaccesstoken !== null;
    }

    /**
     * @return null
     * @todo move me to a collaborator
     */
    public function clearOAuthData()
    {
        $this->identityCache = null;
        $this->oauthidentitycache = null;
        $clearCacheQuery = "UPDATE user SET oauthidentitycache = NULL WHERE id = :id;";
        $this->dbObject->prepare($clearCacheQuery)->execute(array(":id" => $this->id));

        return null;
    }

    /**
     * @throws Exception
     * @todo     move me to a collaborator
     * @category Security-Critical
     */
    private function getIdentityCache()
    {
        /** @var IOAuthHelper $oauthHelper */
        global $oauthHelper;

        try {
            $this->identityCache = $oauthHelper->getIdentityTicket($this->oauthaccesstoken, $this->oauthaccesssecret);

            $this->oauthidentitycache = serialize($this->identityCache);
            $this->dbObject->prepare("UPDATE user SET oauthidentitycache = :identity WHERE id = :id;")
                ->execute(array(":id" => $this->id, ":identity" => $this->oauthidentitycache));
        }
        catch (UnexpectedValueException $ex) {
            $this->identityCache = null;
            $this->oauthidentitycache = null;
            $this->dbObject->prepare("UPDATE user SET oauthidentitycache = NULL WHERE id = :id;")
                ->execute(array(":id" => $this->id));

            SessionAlert::warning("OAuth error getting identity from MediaWiki: " . $ex->getMessage());
        }
    }

    /**
     * @return bool
     * @todo move me to a collaborator
     */
    public function oauthCanUse()
    {
        try {
            return in_array('useoauth', $this->getOAuthIdentity()->grants);
        }
        catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @return bool
     * @todo move me to a collaborator
     */
    public function oauthCanEdit()
    {
        try {
            return in_array('useoauth', $this->getOAuthIdentity()->grants)
            && in_array('createeditmovepage', $this->getOAuthIdentity()->grants)
            && in_array('createtalk', $this->getOAuthIdentity()->rights)
            && in_array('edit', $this->getOAuthIdentity()->rights)
            && in_array('writeapi', $this->getOAuthIdentity()->rights);
        }
        catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @return bool
     * @todo move me to a collaborator
     */
    public function oauthCanCreateAccount()
    {
        try {
            return in_array('useoauth', $this->getOAuthIdentity()->grants)
            && in_array('createaccount', $this->getOAuthIdentity()->grants)
            && in_array('createaccount', $this->getOAuthIdentity()->rights)
            && in_array('writeapi', $this->getOAuthIdentity()->rights);
        }
        catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @return bool
     * @todo     move me to a collaborator
     * @category Security-Critical
     */
    protected function oauthCanCheckUser()
    {
        if (!$this->isOAuthLinked()) {
            return false;
        }

        try {
            $identity = $this->getOAuthIdentity();

            return in_array('checkuser', $identity->rights);
        }
        catch (Exception $ex) {
            return false;
        }
    }

    #endregion

    /**
     * Gets a hash of data for the user to reset their password with.
     * @category Security-Critical
     * @return string
     */
    public function getForgottenPasswordHash()
    {
        return md5($this->username . $this->email . $this->welcome_template . $this->id . $this->password);
    }

    /**
     * Gets the approval date of the user
     * @return DateTime|false
     */
    public function getApprovalDate()
    {
        $query = $this->dbObject->prepare(<<<SQL
			SELECT timestamp 
			FROM log 
			WHERE objectid = :userid
				AND objecttype = 'User'
				AND action = 'Approved' 
			ORDER BY id DESC 
			LIMIT 1;
SQL
        );
        $query->execute(array(":userid" => $this->id));

        $data = DateTime::createFromFormat("Y-m-d H:i:s", $query->fetchColumn());
        $query->closeCursor();

        return $data;
    }
}

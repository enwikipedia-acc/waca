<?php

/**
 * User data object
 */
class User extends DataObject
{
	private $username;
	private $email;
	private $password;
	private $status = "New";
	private $onwikiname = "##OAUTH##";
	private $welcome_sig = "";
	private $lastactive = "0000-00-00 00:00:00";
	private $forcelogout = 0;
	private $checkuser = 0;
	private $identified = 0;
	private $welcome_template = 0;
	private $abortpref = 0;
	private $confirmationdiff = 0;
	private $emailsig = "";
	private $oauthrequesttoken = null;
	private $oauthrequestsecret = null;
	private $oauthaccesstoken = null;
	private $oauthaccesssecret = null;
	private $oauthidentitycache = null;

	// cache variable of the current user - it's never going to change in the middle of a request.
	private static $currentUser;

	private $identityCache = null;

	#region Object load methods

	/**
	 * Gets the currently logged in user
	 * @param PdoDatabase $database
	 * @return User|CommunityUser
	 */
	public static function getCurrent(PdoDatabase $database = null)
	{
		if ($database === null) {
			$database = gGetDb();   
		}

		if (self::$currentUser === null) {
			if (isset($_SESSION['userID'])) {
				self::$currentUser = self::getById($_SESSION['userID'], $database);
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
	 * @param int         $id
	 * @param PdoDatabase $database
	 * @return CommunityUser|User|false
	 */
	public static function getById($id, PdoDatabase $database)
	{
		if ($id == "-1") {
			return new CommunityUser();
		}

		return parent::getById($id, $database);
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
	 * @param  string      $username
	 * @param  PdoDatabase $database
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
			$resultObject->isNew = false;
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
	 * @param string $username 
	 * @param PdoDatabase $database 
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
			$resultObject->isNew = false;
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
			$user->isNew = false;
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
			$user->isNew = false;
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
	 * @return User|false
	 */
	public static function getByRequestToken($requestToken, PdoDatabase $database)
	{
		$statement = $database->prepare("SELECT * FROM user WHERE oauthrequesttoken = :id LIMIT 1;");
		$statement->bindValue(":id", $requestToken);

		$statement->execute();

		$resultObject = $statement->fetchObject(get_called_class());

		if ($resultObject != false) {
			$resultObject->isNew = false;
			$resultObject->setDatabase($database); 
		}

		return $resultObject;
	}

	/**
	 * Gets all users with a supplied status
	 *
	 * @param string $status
	 * @param PdoDatabase $database
	 * @return User[]
	 */
	public static function getAllWithStatus($status, PdoDatabase $database)
	{
		$statement = $database->prepare("SELECT * FROM user WHERE status = :status");
		$statement->execute(array(":status" => $status));

		$resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

		/** @var User $u */
		foreach ($resultObject as $u) {
			$u->setDatabase($database);
			$u->isNew = false;
		}

		return $resultObject;
	}

	/**
	 * Gets all checkusers
	 * @param PdoDatabase $database
	 * @return User[]
	 */
	public static function getAllCheckusers(PdoDatabase $database)
	{
		$statement = $database->prepare("SELECT * FROM user WHERE checkuser = 1;");
		$statement->execute();

		$resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

		$resultsCollection = array();

		/** @var User $u */
		foreach ($resultObject as $u) {
			$u->setDatabase($database);
			$u->isNew = false;

			if (!$u->isCheckuser()) {
				continue;
			}

			$resultsCollection[] = $u;
		}

		return $resultsCollection;
	}

	/**
	 * Gets all inactive users
	 * @param PdoDatabase $database
	 * @return User[]
	 */
	public static function getAllInactive(PdoDatabase $database)
	{
		$date = new DateTime();
		$date->modify("-45 days");

		$statement = $database->prepare(<<<SQL
			SELECT * 
			FROM user 
			WHERE lastactive < :lastactivelimit 
				AND status != 'Suspended' 
				AND status != 'Declined' 
				AND status != 'New' 
			ORDER BY lastactive ASC;
SQL
		);
		$statement->execute(array(":lastactivelimit" => $date->format("Y-m-d H:i:s")));

		$resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

		/** @var User $u */
		foreach ($resultObject as $u) {
			$u->setDatabase($database);
			$u->isNew = false;
		}

		return $resultObject;
	}

	/**
	 * Gets all the usernames in the system
	 * @param PdoDatabase $database
	 * @param null|bool|string $filter If null, no filter. If true, active users only, otherwise provided status.
	 * @return string[]
	 */
	public static function getAllUsernames(PdoDatabase $database, $filter = null)
	{
		if ($filter === null) {
			$userListQuery = "SELECT username FROM user;";
			$userListResult = $database->query($userListQuery);
		}
		elseif ($filter === true) {
			$userListQuery = "SELECT username FROM user WHERE status IN ('User', 'Admin');";
			$userListResult = $database->query($userListQuery);
		}
		else {
			$userListQuery = "SELECT username FROM user WHERE status = :status;";
			$userListResult = $database->prepare($userListQuery);
			$userListResult->execute(array(":status" => $filter));
		}
		
		return $userListResult->fetchAll(PDO::FETCH_COLUMN);
	}

	#endregion

	/**
	 * Saves the current object
	 *
	 * @throws Exception
	 */
	public function save()
	{
		if ($this->isNew) {
// insert
			$statement = $this->dbObject->prepare(<<<SQL
				INSERT INTO `user` ( 
					username, email, password, status, onwikiname, welcome_sig, 
					lastactive, forcelogout, checkuser, identified, 
					welcome_template, abortpref, confirmationdiff, emailsig, 
					oauthrequesttoken, oauthrequestsecret, 
					oauthaccesstoken, oauthaccesssecret
				) VALUES (
					:username, :email, :password, :status, :onwikiname, :welcome_sig,
					:lastactive, :forcelogout, :checkuser, :identified, 
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
			$statement->bindValue(":checkuser", $this->checkuser);
			$statement->bindValue(":identified", $this->identified);
			$statement->bindValue(":welcome_template", $this->welcome_template);
			$statement->bindValue(":abortpref", $this->abortpref);
			$statement->bindValue(":confirmationdiff", $this->confirmationdiff);
			$statement->bindValue(":emailsig", $this->emailsig);
			$statement->bindValue(":ort", $this->oauthrequesttoken);
			$statement->bindValue(":ors", $this->oauthrequestsecret);
			$statement->bindValue(":oat", $this->oauthaccesstoken);
			$statement->bindValue(":oas", $this->oauthaccesssecret);
            
			if ($statement->execute()) {
				$this->isNew = false;
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
					checkuser = :checkuser, identified = :identified,
					welcome_template = :welcome_template, abortpref = :abortpref, 
					confirmationdiff = :confirmationdiff, emailsig = :emailsig, 
					oauthrequesttoken = :ort, oauthrequestsecret = :ors, 
					oauthaccesstoken = :oat, oauthaccesssecret = :oas 
				WHERE id = :id 
				LIMIT 1;
SQL
			);
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":username", $this->username);
			$statement->bindValue(":email", $this->email);
			$statement->bindValue(":password", $this->password);
			$statement->bindValue(":status", $this->status);
			$statement->bindValue(":onwikiname", $this->onwikiname);
			$statement->bindValue(":welcome_sig", $this->welcome_sig);
			$statement->bindValue(":lastactive", $this->lastactive);
			$statement->bindValue(":forcelogout", $this->forcelogout);
			$statement->bindValue(":checkuser", $this->checkuser);
			$statement->bindValue(":identified", $this->identified);
			$statement->bindValue(":welcome_template", $this->welcome_template);
			$statement->bindValue(":abortpref", $this->abortpref);
			$statement->bindValue(":confirmationdiff", $this->confirmationdiff);
			$statement->bindValue(":emailsig", $this->emailsig);
			$statement->bindValue(":ort", $this->oauthrequesttoken);
			$statement->bindValue(":ors", $this->oauthrequestsecret);
			$statement->bindValue(":oat", $this->oauthaccesstoken);
			$statement->bindValue(":oas", $this->oauthaccesssecret);
            
			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		} 
	}

	/**
	 * Authenticates the user with the supplied password
	 *
	 * @param $password
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

	/**
	 * Updates the last login attribute
	 * @todo This should probably update the object too.
	 */
	public function touchLastLogin()
	{
		$query = "UPDATE user SET lastactive = CURRENT_TIMESTAMP() WHERE id = :id;";
		$this->dbObject->prepare($query)->execute(array(":id" => $this->id));
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
	 * @param string $username
	 */
	public function setUsername($username)
	{
		$this->username = $username;
		$this->forcelogout = 1;
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
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * Sets the user's password
	 * @param string $password the plaintext password
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
	 * Gets the user's on-wiki name
	 * @return string
	 */
	public function getOnWikiName()
	{
		if ($this->oauthaccesstoken != null) {
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
	 * @param $onWikiName
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
	 * @param string $welcomeSig
	 */
	public function setWelcomeSig($welcomeSig)
	{
		$this->welcome_sig = $welcomeSig;
	}

	/**
	 * Gets the last activity date for the user
	 *
	 * @see touchLastLogin()
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
	 * @todo this should return a bool to match the setter, plus a rename
	 * @return int
	 */
	public function getForcelogout()
	{
		return $this->forcelogout;
	}

	/**
	 * Sets the user's forced logout status
	 * @param $forceLogout bool
	 * @todo Rename me please!
	 */
	public function setForcelogout($forceLogout)
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
	 * @param $welcomeTemplate
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
	 * @param string $oAuthRequestToken
	 */
	public function setOAuthRequestToken($oAuthRequestToken)
	{
		$this->oauthrequesttoken = $oAuthRequestToken;
	}

	/**
	 * Gets the users OAuth request secret
	 * @category Security-Critical
	 * @todo move me to a collaborator
	 * @return null|string
	 */
	public function getOAuthRequestSecret()
	{
		return $this->oauthrequestsecret;
	}

	/**
	 * Sets the user's OAuth request secret
	 * @todo move me to a collaborator
	 * @param $oAuthRequestSecret
	 */
	public function setOAuthRequestSecret($oAuthRequestSecret)
	{
		$this->oauthrequestsecret = $oAuthRequestSecret;
	}

	/**
	 * Gets the user's access token
	 * @category Security-Critical
	 * @todo move me to a collaborator
	 * @return null|string
	 */
	public function getOAuthAccessToken()
	{
		return $this->oauthaccesstoken;
	}

	/**
	 * Sets the user's access token
	 * @todo move me to a collaborator
	 * @param $oAuthAccessToken
	 */
	public function setOAuthAccessToken($oAuthAccessToken)
	{
		$this->oauthaccesstoken = $oAuthAccessToken;
	}

	/**
	 * Gets the user's OAuth access secret
	 * @category Security-Critical
	 * @todo move me to a collaborator
	 * @return null
	 */
	public function getOAuthAccessSecret()
	{
		return $this->oauthaccesssecret;
	}

	/**
	 * Sets the user's OAuth access secret
	 * @todo move me to a collaborator
	 * @param $oAuthAccessSecret
	 */
	public function setOAuthAccessSecret($oAuthAccessSecret)
	{
		$this->oauthaccesssecret = $oAuthAccessSecret;
	}

	#endregion
    
	#region changing access level

	/**
	 * Approves the user, changing access to 'User'
	 * @category Security-Critical
	 */
	public function approve()
	{
		$this->dbObject->transactionally(function()
		{
			$this->status = "User";
			$this->save();
			Logger::approvedUser($this->dbObject, $this);
		});
	}

	/**
	 * Suspends the user
	 * @category Security-Critical
	 * @param $comment
	 */
	public function suspend($comment)
	{
		$this->dbObject->transactionally(function() use ($comment)
		{
			$this->status = "Suspended";
			$this->save();
			Logger::suspendedUser($this->dbObject, $this, $comment);
		});
	}

	/**
	 * Declines the user (from new => declined)
	 * @category Security-Critical
	 * @param $comment
	 */
	public function decline($comment)
	{
		$this->dbObject->transactionally(function() use ($comment)
		{
			$this->status = "Declined";
			$this->save();
			Logger::declinedUser($this->dbObject, $this, $comment);
		});
	}

	/**
	 * Promotes the user to tool administrator
	 * @category Security-Critical
	 */
	public function promote()
	{
		$this->dbObject->transactionally(function()
		{
			$this->status = "Admin";
			$this->save();
			Logger::promotedUser($this->dbObject, $this);
		});
	}

	/**
	 * Demotes the user to a standard user
	 * @category Security-Critical
	 * @param $comment
	 */
	public function demote($comment)
	{
		$this->dbObject->transactionally(function() use ($comment)
		{
			$this->status = "User";
			$this->save();
			Logger::demotedUser($this->dbObject, $this, $comment);
		});
	}

	#endregion
    
	#region user access checks

	/**
	 * Tests if the user is an admin
	 * @return bool
	 * @category Security-Critical
	 */
	public function isAdmin()
	{
		return $this->status == "Admin";
	}

	/**
	 * Tests if the user is a checkuser
	 * @return bool
	 * @category Security-Critical
	 */
	public function isCheckuser()
	{
		return $this->checkuser == 1 || $this->oauthCanCheckUser();
	}

	/**
	 * Tests if the user is identified
	 * @return bool
	 * @category Security-Critical
	 */
	public function isIdentified()
	{
		return $this->identified === 1;
	}

	/**
	 * Tests if the user is suspended
	 * @return bool
	 * @category Security-Critical
	 */
	public function isSuspended()
	{
		return $this->status == "Suspended";
	}

	/**
	 * Tests if the user is new
	 * @return bool
	 * @category Security-Critical
	 */
	public function isNew()
	{
		return $this->status == "New";
	}

	/**
	 * Tests if the user is a standard-level user
	 * @return bool
	 * @category Security-Critical
	 */
	public function isUser()
	{
		return $this->status == "User";
	}

	/**
	 * Tests if the user has been declined access to the tool
	 * @return bool
	 * @category Security-Critical
	 */
	public function isDeclined()
	{
		return $this->status == "Declined";
	}

	/**
	 * Tests if the user is the community user
	 *
	 * @todo decide if this means logged out. I think it usually does.
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
	 * @todo move me to a collaborator
	 * @param bool $useCached
	 * @return mixed|null
	 * @category Security-Critical
	 */
	public function getOAuthIdentity($useCached = false)
	{
		if ($this->oauthaccesstoken == null) {
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
	 * @todo move me to a collaborator
	 * @param mixed $useCached Set to false for everything where up-to-date data is important.
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
	private function clearOAuthData()
	{
		$this->identityCache = null;
		$this->oauthidentitycache = null;
		$clearCacheQuery = "UPDATE user SET oauthidentitycache = null WHERE id = :id;";
		$this->dbObject->prepare($clearCacheQuery)->execute(array(":id" => $this->id));
        
		return null;
	}

	/**
	 * @throws Exception
	 * @throws TransactionException
	 * @todo move me to a collaborator
	 * @category Security-Critical
	 */
	private function getIdentityCache()
	{
		global $oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal;
        
		try {
			$util = new OAuthUtility($oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal);
			$this->identityCache = $util->getIdentity($this->oauthaccesstoken, $this->oauthaccesssecret);
			$this->oauthidentitycache = serialize($this->identityCache);
			$this->dbObject->
				prepare("UPDATE user SET oauthidentitycache = :identity WHERE id = :id;")->
				execute(array(":id" => $this->id, ":identity" => $this->oauthidentitycache));
		}
		catch (UnexpectedValueException $ex) {
			$this->identityCache = null;
			$this->oauthidentitycache = null;
			$this->dbObject->
				prepare("UPDATE user SET oauthidentitycache = null WHERE id = :id;")->
				execute(array(":id" => $this->id));

			SessionAlert::warning("OAuth error getting identity from MediaWiki: " . $ex->getMessage());
		}   
	}

	/**
	 * @throws Exception
	 * @todo move me to a collaborator
	 */
	public function detachAccount()
	{
		$this->setOnWikiName($this->getOAuthOnWikiName());
		$this->setOAuthAccessSecret(null);
		$this->setOAuthAccessToken(null);
		$this->setOAuthRequestSecret(null);
		$this->setOAuthRequestToken(null);

		$this->clearOAuthData();
        
		$this->save();
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
				&& in_array('writeapi', $this->getOAuthIdentity()->rights); }
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
	 * @todo move me to a collaborator
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

	/**
	 * Gets a user-visible description of the object.
	 * @return string
	 */
	public function getObjectDescription()
	{
		$username = htmlentities($this->username, ENT_COMPAT, 'UTF-8');
		return '<a href="statistics.php?page=Users&amp;user=' . $this->getId() . '">' . $username . "</a>";
	}
}

<?php

/**
 * User class, describes everything that can be done with a user.
 * @author stwalkerster
 *
 */
class User
{
	/**
	 * Gets the current user logged in from the session
	 * @return User the currently logged in user
	 */
	public static function getCurrentUser()
	{
		return User::getByName($_SESSION['user']);
	}

	/**
	 * Gets the user with the specified ID
	 * @param integer $id The ID of the user
	 * @return User the user with the specifed ID
	 */
	public static function getById($id)
	{

	}

	/**
	 * Gets the user with the specified username
	 * @param string $id The username of the user
	 * @return User the user with the specifed username
	 */
	public static function getByName($name)
	{
		
	}


	/**
	 * Encrypts a password for storing in the database or authentication
	 * @param string $password The plaintext password
	 * @param integer $version The version of the encryption algorithm to use
	 * @return string the encrypted password string
	 */
	private function encryptPassword($password, $version=1)
	{
		return md5($password);
	}

	public function __construct($username, $password, $wikiname, $email, $usesecure)
	{
		$this->uIsNew = 1;
	}

	private $uIsNew = 0;
	private $uId = 0;
	private $uName = "";
	private $uEmail = "";
	private $uPassword = "";
	private $uAccessLevel = "";
	private $uOnwikiName = "";
	private $uWelcomeSignature = "";
	private $uLastActive = "";
	private $uLastIp = "";
	private $uForceLogout = "";
	private $uSecureServer = "";
	private $uIsCheckuser = "";
	private $uIsIdentified = "";
	private $uWelcomeTemplateId = "";

	public function save()
	{
		if($this->isNew)
		{
			// INSERT
		}
		else
		{
			// UPDATE
		}
	}

	public function authenticate($password)
	{
		return
		$this->uPassword == $this->encryptPassword($password) ?
		true : false;
	}

	public function setPassword($newPassword)
	{
		$this->uPassword = $this->encryptPassword($newPassword);
	}

	public function getName() { return $uName;}

	public function getEmail() { return $uEmail;}
	public function setEmail($newEmail){$uEmail = $newEmail;}

	public function getAccessLevel() { return $uAccessLevel;}

	public function getOnwikiName() { return $uOnwikiName;}
	public function setOnwikiName($newOnwikiName){$uOnwikiName = $newOnwikiName;}

	public function getWelcomeSignature() { return $uWelcomeSignature;}
	public function setWelcomeSignature($newWelcomeSignature){$uWelcomeSignature = $newWelcomeSignature;}

	public function getLastActive() { return $uLastActive;}

	public function getLastIp() { return $uLastIp;}

	public function getForceLogout() { return $uForceLogout;}

	public function getSecureServer() { return $uSecureServer;}
	public function setSecureServer($newSecureServer){$uSecureServer = $newSecureServer;}

	public function getIsCheckuser() { return $uIsCheckuser;}

	public function getIsIdentified() { return $uIsIdentified;}

	public function getWelcomeTemplateId() { return $uWelcomeTemplateId;}
	public function setWelcomeTemplateId($newWelcomeTemplateId){$uWelcomeTemplateId = $newWelcomeTemplateId;}



	//---------


	public function rename($newName){$uName = $newName;}
	public function promote(){}
	public function demote($reason){}
	public function decline($reason){}
	public function approve(){}
	public function suspend($reason){}
	
	
	/**
	 * Sets the last access time for the user
	 */
	public function touch(){
		$uLastIp = $_SERVER['REMOTE_ADDR'];
		$uLastActive = date(DATE_ATOM);
	}

}
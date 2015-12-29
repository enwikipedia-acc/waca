<?php

/**
 * User data object
 */
class CommunityUser extends User
{
	public function getId()
	{
		return -1;
	}
    
	public function save()
	{
		// Do nothing
	}

	public function authenticate($password)
	{
		// Impossible to log in as this user
		return false;
	}
    
	public function touchLastLogin()
	{
	}
    
	#region properties
    
	public function getUsername()
	{
		global $communityUsername;
		return $communityUsername;
	}

	public function setUsername($username)
	{
	}

	public function getEmail()
	{
		global $cDataClearEmail;
		return $cDataClearEmail;
	}

	public function setEmail($email)
	{
	}

	public function setPassword($password)
	{
	}

	public function getStatus()
	{
		return "Community";
	}

	public function getOnWikiName()
	{
		return "127.0.0.1";
	}
    
	public function getStoredOnWikiName()
	{
		return $this->getOnWikiName();
	}

	public function setOnWikiName($onwikiname)
	{
	}

	public function getWelcomeSig()
	{
		return null;
	}

	public function setWelcomeSig($welcomesig)
	{
	}

	public function getLastActive()
	{
		$now = new DateTime();
		return $now->format("Y-m-d H:i:s");
	}

	public function setLastActive($lastactive)
	{
	}

	public function getForcelogout()
	{
		return 1;
	}

	public function setForcelogout($forcelogout)
	{
	}
    
	public function getSecure()
	{
		return true;
	}

	public function getCheckuser()
	{
		return false;
	}

	public function setCheckuser($checkuser)
	{
	}

	public function getIdentified()
	{
		return false;
	}

	public function setIdentified($identified)
	{
	}

	public function getWelcomeTemplate()
	{
		return 0;
	}

	public function setWelcomeTemplate($welcometemplate)
	{
	}

	public function getAbortPref()
	{
		return 0;
	}

	public function setAbortPref($abortpref)
	{
	}

	public function getConfirmationDiff()
	{
		return null;
	}

	public function setConfirmationDiff($confirmationdiff)
	{
	}

	public function getEmailSig()
	{
		return null;
	}

	public function setEmailSig($emailsig)
	{
	}
    
	#endregion
    
	#region changing access level
	public function approve()
	{
	}
    
	public function suspend($comment)
	{
	}
    
	public function decline($comment)
	{
	}
    
	public function promote()
	{
	}
    
	public function demote($comment)
	{
	}

	#endregion
    
	#region user access checks
    
	public function isAdmin()
	{
		return false;
	}
    
	public function isCheckuser()
	{
		return false;
	}
    
	public function isIdentified()
	{
		return false;
	}
    
	public function isSuspended()
	{
		return false;
	}
    
	public function isNew()
	{
		return false;
	}
    
	public function isUser()
	{
		return false;
	}
    
	public function isDeclined()
	{
		return false;
	}
    
	public function isCommunityUser()
	{
		return true;
	}
    
	#endregion 

	#region OAuth
    
	public function getOAuthIdentity($useCached = false)
	{
		return null;
	}
    
	public function isOAuthLinked()
	{
		return false;
	}
    
	public function detachAccount()
	{
	}
    
	public function oauthCanUse()
	{
		return false;
	}
    
	public function oauthCanEdit()
	{
		return false;
	}
    
	public function oauthCanCreateAccount()
	{
		return false;
	}

	public function oauthCanCheckUser()
	{
		return false;
	}
    
	#endregion

	public function getApprovalDate()
	{
		$data = DateTime::createFromFormat("Y-m-d H:i:s", "1970-01-01 00:00:00");
		return $data;
	}
}

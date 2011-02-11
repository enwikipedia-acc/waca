<?php

class User
{
	static function getCurrentUser()
	{
		
	}	
	
	static function getById($id)
	{
		
	}
	
	static function getByName()
	{
		
	}
	
	private static function encryptPassword($password)
	{
		return md5($password);
	}
	
	private $uIsNew = 0;
	private $uPassword = "";
	
	function __construct($username, $password, $wikiname, $email, $usesecure)
	{
		$this->uIsNew = 1;
	}
	
	function save()
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

	function authenticate($password)
	{
		return
			$this->uPassword == User::encryptPassword($password) ?
			true : false;
	}

	function setPassword($newPassword)
	{
		$this->uPassword = User::encryptPassword($newPassword);
	}
}
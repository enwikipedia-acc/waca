<?php

/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
** SQL ( http://en.wikipedia.org/User:SQL )                 **
** Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
** FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
** Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
** Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
** Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
** OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
** Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
** Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**                                                           **
**************************************************************/

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

// Get all the classes.
require_once 'config.inc.php';

class database {	
	private $dbLink;
	private $host;
	
	public function __construct($name, $link) {
	
		if($name==='toolserver')
		{
			global $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;
			$this->connect($link, $toolserver_host, $toolserver_username, $toolserver_password, $toolserver_database);
		}
		elseif($name==='antispoof')
		{
			global $dontUseWikiDb;							
			if($dontUseWikiDb == 0)
			{
				global $antispoof_host, $antispoof_db, $antispoof_table, $toolserver_username, $toolserver_password;
				$this->connect($link, $antispoof_host, $toolserver_username, $toolserver_password, $antispoof_db);
			}
		}
	}
	
	private function connect($link, $host, $username, $password, $database) {
		$this->dbLink = mysql_pconnect($host,$username,$password) or $this->showError("Error connecting to database ($database on $host): ".$this->getError(),'Error connecting to database.');
		if($link)
		{
			$this->selectDb($database);
		}
		$this->host = $host;
	}
	
	private function selectDb($database) {
		// TODO: Improve error msg and handling
		mysql_select_db($database,$this->dbLink) or $this->showError("Error selecting $database on ".$this->host.": ".$this->getError(),'Error selecting the database.');
	}
	
	public function query($query) {
		return mysql_query($query,$this->dbLink);
	}
	
	public function escape($string) {
		// WARNING: This does not escape against XSS, this is intentional to avoid double escape etc
		// please escape user input seperately using htmlentities()
		return mysql_real_escape_string($string,$this->dbLink);
	}
	
	public function showError($sql_error,$generic_error=null) {
		global $enableSQLError;
		if ($generic_error==null) {
			$generic_error = $sql_error;
		}
		if ($enableSQLError) {
			die($sql_error);
		} else {
			die($generic_error);
		}
	}
	
	public function getError() {
		return mysql_error($this->dbLink);
	}
	
	// public function __destruct() {
	//	  mysql_close($this->dbLink);
	// }
}
?>
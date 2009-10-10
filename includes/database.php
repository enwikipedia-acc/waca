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
	private $dbLink, $host, $db;
	
	public function __construct($name) {

		// Checks to which database should be connected.
		if($name==='toolserver') {
			global $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;
			$this->connect($toolserver_host, $toolserver_username, $toolserver_password, $toolserver_database);
			
			// Assigns the specific databases's name to be used later.
			$this->db = $name;
		}
		elseif($name==='antispoof') {
			// Checks whether the WikiDB may be used.
			global $dontUseWikiDb;					
			if($dontUseWikiDb == 0) {
				global $antispoof_host, $antispoof_db, $antispoof_table, $toolserver_username, $toolserver_password;
				$this->connect($antispoof_host, $toolserver_username, $toolserver_password, $antispoof_db);
			
				// Assigns the specific databases's name to be used later.
				$this->db = $name;
			}
		}
	}
	
	private function connect($host, $username, $password, $database) {
		$this->dbLink = mysql_pconnect($host,$username,$password) or $this->showError("Error connecting to database ($database on $host): ".$this->getError(),'Error connecting to database.');
		
		// Connects to the required database.
		$this->selectDb($database);
		
		// Assigns the specific host's name to be used later. 
		$this->host = $host;		
	}
	
	// Function to only generate a link to the database.
	public function getLink() {		
		global $link;
		
		// Uses the earlier assigned database name.
		if($this->db === "toolserver")
		{
			global $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;
			$link = mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
		}
		elseif($this->db === "antispoof")
			{
			global $antispoof_host, $antispoof_db, $antispoof_password, $dontUseWikiDb;
			if(!$dontUseWikiDb) {
				$link = mysql_pconnect($antispoof_host, $toolserver_username, $antispoof_password);
			}
		}
		// Return the link.
		return $link;
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
			$generic_error = "The tool has encountered a database error, and cannot continue loading this page. Please try again later.";
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
	
	// The database is connected on a passive manner.
	// Because of this we cannot call mysql_close().
	// public function __destruct() {
	//	  mysql_close($this->dbLink);
	// }
}
?>
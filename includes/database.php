<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/
if (!defined("ACC")) {
	die();
} // Invalid entry point

// Get all the classes.
require_once 'config.inc.php';

/**
 * This class is used to comunicate with the Toolserver and Antispoof databases.
 */
class database {
	private $dbLink, $host, $db;
	
	/**
	 * Creates a new instance of the database class.
	 * @param $name Which database to connect to. { "toolserver" | "antispoof" }
	 * @return New instance of the particular database class.
	 */
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

				$this->query("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
			}
		}
		elseif($name==='notif') {
				global $toolserver_username, $toolserver_password, $toolserver_notification_database, $toolserver_notification_dbhost;
				$this->connect($toolserver_notification_dbhost, $toolserver_username, $toolserver_password, $toolserver_notification_database);

				// Assigns the specific databases's name to be used later.
				$this->db = $name;
		}
	}
	
	/**
	 * Connects completely to the particular database.
	 * @param $host The MySQL server.
	 * @param $username The username.
	 * @param $password The password.
	 * @param $database The name of the database that is to be selected.
	 */
	private function connect($host, $username, $password, $database) {
		$this->dbLink = mysql_pconnect($host,$username,$password) or $this->showError("Error connecting to database ($database on $host): ".$this->getError(),'Error connecting to database.');
		
		// Connects to the required database.
		$this->selectDb($database);
		
		// Assigns the specific host's name to be used later. 
		$this->host = $host;
	}
	
	/**
	 * Only generate a link to the database.
	 * @return A MySQL persistent link identifier.
	 */
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
			global $antispoof_host, $antispoof_db, $antispoof_password, $toolserver_username, $dontUseWikiDb;
			if(!$dontUseWikiDb) {
				$link = mysql_pconnect($antispoof_host, $toolserver_username, $antispoof_password);
			}
		}
		elseif($this->db === "notif")
		{
			global $toolserver_username, $toolserver_password;

			$link = mysql_pconnect("dbmaster.helpmebot.org.uk", $toolserver_username, $toolserver_password);
		}
		// Return the link.
		return $link;
	}
	
	/**
	 * Select a MySQL database.
	 * @param $database The name of the database that is to be selected.
	 */
	private function selectDb($database) {
		// TODO: Improve error msg and handling
		mysql_select_db($database,$this->dbLink) or $this->showError("Error selecting $database on ".$this->host.": ".$this->getError(),'Error selecting the database.');
	}
	
	/**
	 * Run a MySQL query on the database.
	 * @param $query The SQL query to execute. The query string should not end with a semicolon. Data inside the query should be properly escaped.
	 * @return MySQL query result.
	 */
	public function query($query) {
		return mysql_query($query,$this->dbLink);
	}
	
	/**
	 * Run a query on the database, pushing the results into an array.
	 * @param $query The SQL query to execute.
	 * @param $result Reference array, set to contain results of query.
	 * @return bool: Whether the query succeded.
	 */
	public function queryToArray($query, &$result)
	{
		$queryResult = $this->query($query);
		if(!$queryResult)
		{
			return false;
		}
		
		$result = array();
		$i=0;
		while($row = mysql_fetch_assoc($queryResult))
		{
			$result[$i] = $row;
			$i++;
		}
		return true;
	}
	
	/**
	 * Escapes a string for MySQL.
	 * @param $string The string to escape
	 * @return The escaped string.
	 */
	public function escape($string) {
		// WARNING: This does not escape against XSS, this is intentional to avoid double escape etc.
		// Please escape user input seperately using htmlentities().
		return mysql_real_escape_string($string,$this->dbLink);
	}
	
	/**
	 * Shows either the SQL error, or a generic error, depending on the configuration of the tool instance.
	 * @param $sql_error Info-rich message giving the actual error message provided by the database.
	 * @param $generic_error Generic error message, used to tell people there's a problem, but not too much information.
	 */
	public function showError($sql_error,$generic_error=null) {
		global $enableSQLError;
		if ($generic_error==null) {
			$generic_error = "The tool has encountered a database error, and cannot continue loading this page. Please try again later.";
		}
		if ($enableSQLError) {
			die("(db ".$this->db.")" . $sql_error);
		} else {
			die($generic_error);
		}
	}
	
	/**
	 * Returns the text of the error message from previous MySQL function.
	 * @return The error text from the last MySQL function.
	 */
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

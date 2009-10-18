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

class session {

	public function setForceLogout( $uid ) {
		$uid = sanitize( $uid );
		global $toolserver_username;
		global $toolserver_password;
		global $toolserver_host;
		global $toolserver_database;
		mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
		$link = mysql_select_db($toolserver_database);
		if( !$link ) {
			sqlerror(mysql_error(),"Error selecting database.");
		}
		$query = "UPDATE acc_user SET user_forcelogout = '1' WHERE user_id = '$uid';";
		$result = mysql_query($query);
	}
	
	public function forceLogout( $uid ) {
		$uid = sanitize( $uid );
		global $toolserver_username;
		global $toolserver_password;
		global $toolserver_host;
		global $toolserver_database;
		mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
		$link = mysql_select_db($toolserver_database);
		if( !$link ) { 
			sqlerror(mysql_error(),"Error selecting database.");	
		}
		$query = "SELECT user_forcelogout FROM acc_user WHERE user_id = '$uid';";
		$result = mysql_query($query);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
		$row = mysql_fetch_assoc($result);
		if( $row['user_forcelogout'] == "1" ) {
			$_SESSION = array();
			if (isset($_COOKIE[session_name()])) {
				setcookie(session_name(), '', time()-42000, '/');
			}
			session_destroy( );
			echo "You have been forcibly logged out, probably due to being renamed. Please log back in.";
			$query = "UPDATE acc_user SET user_forcelogout = '0' WHERE user_id = '$uid';";
			$result = mysql_query($query);
			die( showfootern( ) );
		}
	}
	
	public function hasright($username, $checkright) {
		global $tsSQL;
		$username = $tsSQL->escape($username);
		$query = "SELECT * FROM acc_user WHERE user_name = '$username';";
		$result = $tsSQL->query($query);
		if (!$result) {
			$tsSQL->showError("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
		}
		$row = mysql_fetch_assoc($result);
		$rights = explode(':', $row['user_level']);
		foreach( $rights as $right) {
			if($right == $checkright ) {
				return true;
			}
		}
		return false;
	}
}
?>
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
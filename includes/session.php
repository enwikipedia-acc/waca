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
	
	public function isCheckuser($username) {
		global $tsSQL;
		$username = $tsSQL->escape($username);
		$query = "SELECT user_checkuser FROM acc_user WHERE user_name = '$username';";
		$result = $tsSQL->query($query);
		if (!$result) {
			$tsSQL->showError("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
			return false;
		}
		$row = mysql_fetch_assoc($result);
		return $row['user_checkuser'];
	}
	
	public function checksecurity($username) {
		/*
		* Check the user's security level on page load, and bounce accordingly
		*/
		global $secure, $session;
		if ($session->hasright($username, "New")) {
			echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
			echo showfootern();
			die();
		} elseif ($session->hasright($username, "Suspended")) {
			echo "I'm sorry, but, your account is presently suspended.<br />\n";
			echo showfootern();
			die();
		} elseif ($session->hasright($username, "Declined")) {
			$username = sanitize($username);
			$query = "SELECT * FROM acc_user WHERE user_name = '$username';";
			$result = mysql_query($query);
			if (!$result) {
				sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
			}
			$row = mysql_fetch_assoc($result);
			$query2 = "SELECT * FROM acc_log WHERE log_pend = '" . $row['user_id'] . "' AND log_action = 'Declined' ORDER BY log_id DESC LIMIT 1;";
			$result2 = mysql_query($query2);
			if (!$result2) {
				sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
			}
			$row2 = mysql_fetch_assoc($result2);
			echo "I'm sorry, but, your account request was <strong>declined</strong> by <strong>" . $row2['log_user'] . "</strong> because <strong>\"" . $row2['log_cmt'] . "\"</strong> at <strong>" . $row2['log_time'] . "</strong>.<br />\n";
			echo "Related information (please include this if appealing this decision)<br />\n";
			echo "user_id: " . $row['user_id'] . "<br />\n";
			echo "user_name: " . $row['user_name'] . "<br />\n";
			echo "user_onwikiname: " . $row['user_onwikiname'] . "<br />\n";
			echo "user_email: " . $row['user_email'] . "<br />\n";
			echo "log_id: " . $row2['log_id'] . "<br />\n";
			echo "log_pend: " . $row2['log_pend'] . "<br />\n";
			echo "log_user: " . $row2['log_user'] . "<br />\n";
			echo "log_time: " . $row2['log_time'] . "<br />\n";
			echo "log_cmt: " . $row2['log_cmt'] . "<br />\n";
			echo "<br /><big><strong>To appeal this decision, please e-mail <a href=\"mailto:accounts-enwiki-l@lists.wikimedia.org\">accounts-enwiki-l@lists.wikimedia.org</a> with the above information, and a reasoning why you believe you should be approved for this interface.</strong></big><br />\n";
			echo showfootern();
			die();
		} elseif ($session->hasright($username, "User") || $session->hasright($username, "Admin") ) {
			$secure = 1;
		} else {
			//die("Not logged in!");
		}
	}
	
	public function getUsernameFromUid($userid)
	{
		/**
		* Retrieves a username from a user id
		*/
		if (!preg_match('/^[0-9]*$/',$userid)) {
			die('Invalid user id. <!-- in function getUsernameFromUid -->');
		}
		$query = "SELECT user_name FROM acc_user WHERE user_id = $userid;";
		$result = mysql_query($query);
		if (!$result)
			Die("Error determining user from UID.");
		$row = mysql_fetch_assoc($result);
		return $row['user_name'];
		$result = mysql_query($query);
		if (!$result)
			Die("Error determining user from UID.");
	}
}
?>
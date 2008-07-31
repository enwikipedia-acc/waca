<?php
require_once ( 'config.inc.php' );
require_once ( 'devlist.php' );
require_once ( 'functions.php' );

//FIXME: displayheader() Stolen from users.php... Maybe these need to be in functions.php?
function displayheader() {
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_emails WHERE mail_id = '8';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo $row['mail_text'];
}
//FIXME: displayfooter() Stolen from users.php... Maybe these need to be in functions.php?
function displayfooter() {
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_emails WHERE mail_id = '7';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo "</div>";
	echo $row['mail_text'];
}

$developer = //Developer info / list.
	array(
		"SQL" =>
			array(						//Set any of these to NULL to keep them from being displayed.
				"IRC" => "SQLDb", 			//IRC Name
				"EMail" => "sxwiki@gmail.com", 		//Public E-mail address
				"wiki" => "SQL", 			//Enwiki Username
				"WWW" => "http://toolserver.org/~sql", 	//Your website
				"Name" => NULL,				//Real name
				"Role" => "Project Lead",		//Project Role(s)
				"Access" => "Shell, SQL, Commit",	//Project Access levels
				"Other" => NULL,			//Anything else, comments, etc.
			),
		"Cobi" =>
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"wiki" => NULL,
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Access" => NULL,
				"Other" => NULL,
			),
		"charlie" =>
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"wiki" => NULL,
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Access" => NULL,
				"Other" => NULL,
			),
		"FastLizard4" =>
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"wiki" => NULL,
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Access" => NULL,
				"Other" => NULL,
			),
		"Stwalkerster" =>
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"wiki" => NULL,
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Access" => NULL,
				"Other" => NULL,
			),
		"Soxred93" =>
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"wiki" => NULL,
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Access" => NULL,
				"Other" => NULL,
			),
		"Alexfusco5" =>
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"wiki" => NULL,
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Access" => NULL,
				"Other" => NULL,
			),
		"OverlordQ" =>
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"wiki" => NULL,
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Access" => NULL,
				"Other" => NULL,
			),
		"Prodego" =>
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"wiki" => NULL,
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Access" => NULL,
				"Other" => NULL,
			),
	);


displayheader();
echo "<h2>ACC Development Team</h2>\n";
foreach( $developer as $devName => $devInfo ) {
	echo "<h3>$devName</h3>\n<ul>\n";
	foreach( $devInfo as $infoName => $infoContent ) {
		if( $infoContent != NULL ) {
			switch( $infoName ) {
				case "IRC":
					echo "<li>IRC Name: $infoContent</li>\n";
					break;
				case "Name":
					echo "<li>Real name: $infoContent</li>\n";
					break;
				case "EMail":
					echo "<li>E-Mail Address: $infoContent</li>\n";
					break;
				case "wiki":
					echo "<li>Enwiki Username: $infoContent</li>\n";
					break;
				case "WWW":
					echo "<li>Homepage: $infoContent</li>\n";
					break;
				case "Role":
					echo "<li>Project Role: $infoContent</li>\n";
					break;
				case "Access":
					echo "<li>Access: $infoContent</li>\n";
					break;
				case "Other":
					echo "<li>Other: $infoContent</li>\n";
					break;
			}
		}
	}
	echo "</ul>";
}
displayfooter();
?>
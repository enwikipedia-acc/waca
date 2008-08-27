<?php

require_once( 'config.inc.php' );
require_once ( 'functions.php' );

session_start();

$link = mysql_connect( $toolserver_host, $toolserver_username, $toolserver_password );
if ( !$link ) {
	die( 'Could not connect: ' . mysql_error( ) );
}
@ mysql_select_db( $toolserver_database ) or print mysql_error( );

echo makehead($_SESSION['user']);
echo '<div id="content">';
echo '<h2>Request search tool (emails only)</h2>';
if( isset($_GET['email']) ) {

echo "<h3>Searching for: $_GET[email] ...</h3>";
	$query = "SELECT pend_id, pend_name, pend_email FROM acc_pend WHERE pend_email LIKE '".$_GET['email']."';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$html = "<table cellspacing=\"0\">\n";
	$currentrow = 0;
	while ( list( $pend_id, $pend_name, $pend_email ) = mysql_fetch_row( $result ) ) {
		$currentrow += 1;
		$out = '<tr';
		if ($currentrow % 2 == 0) {
			$out .= ' class="even">';
		} else {
			$out .= ' class="odd">';
		}
		$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\">Request " . $pend_id . "</a></small></tr>";
		$html .= $out;
	}
	$html .= "</table>\n";
	$html .= "<b>Results found: </b> $currentrow.";
echo $html;
}
else {
?>
<form action="search.php" method="get">
Email: <input type="text" name="email" /><br />
<input type="submit" />
</form>

<?php
}

echo "</div>";
echo showfooter();
?>
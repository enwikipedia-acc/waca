<?php
/************************************************
** English Wikipedia Account Request Interface **
** All code is released into the public domain **
**             Developers:                     **
**  SQL ( http://en.wikipedia.org/User:SQL )   **
**  Cobi ( http://en.wikipedia.org/User:Cobi ) **
**                                             **
************************************************/

function sanitize($what) {
        $what = mysql_real_escape_string($what);
        return($what);
}

require_once('../../database.inc');
mysql_connect("sql",$toolserver_username,$toolserver_password);
@mysql_select_db("u_sql") or print mysql_error();
$query = "SELECT * FROM acc_user";
$result = mysql_query($query);
if(!$result) Die("ERROR: No result returned.");
echo "<h2>User List</h2>\n<ul>\n";
while ($row = mysql_fetch_assoc($result)) {
	echo "<li>$row[user_name]</li>\n";
}
echo "<ul>\n";
?>

<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} // Web clients die.
ini_set('display_errors', 1);
require_once 'config.inc.php';

mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(), "Error selecting database.");
mysql_query("UPDATE acc_pend SET pend_ip = '127.0.0.1', pend_email = 'acc@toolserver.org', `pend_useragent` = '' WHERE pend_date < DATE_SUB(curdate(), interval 3 MONTH);");
mysql_close();

echo "Deletion complete.";
?>

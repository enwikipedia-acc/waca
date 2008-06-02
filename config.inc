<?
$toolserver_mycnf = parse_ini_file("/home/".get_current_user()."/.my.cnf");
$toolserver_username = $toolserver_mycnf['user'];
$toolserver_password = $toolserver_mycnf['password'];
$toolserver_host = $toolserver_mycnf['host'];
$toolserver_database = "u_sql";
$cookiepath = '/~sql/';
$sessionname = 'ACC';
unset($toolserver_mycnf);
?>

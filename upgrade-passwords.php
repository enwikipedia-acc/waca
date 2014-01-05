<?php
// run this script to update the user column in the ban table from usernames to user ids.

if (isset($_SERVER['REQUEST_METHOD'])) {
   die();
} // Web clients die.

ini_set('display_errors', 1);

require_once 'config.inc.php';
require_once 'includes/PdoDatabase.php';

$db = gGetDb( );

$db->exec("CREATE TABLE tmp_userpwbackup AS SELECT id, password FROM user WHERE password NOT LIKE ':%';");

if( ! $db->beginTransaction() ) {
  	echo "Error in transaction: Could not start transaction.";
	exit( 1 );  
}

try
{
    $query = $db->prepare( "SELECT id, password FROM user WHERE password NOT LIKE ':%';" );
    if( ! $query->execute() ) {
        $db->rollback();
        echo "Error in transaction: Could not get data.";
        print_r( $db->errorInfo() );
        exit( 1 );
    }

    $data = $query->fetchAll(PDO::FETCH_ASSOC);

    $query = $db->prepare("UPDATE user SET password = :password WHERE id = :id;");

    foreach($data as $u)
    {
        $salt = microtime();
        $pass = ':1:' . $salt . ':' . md5( $salt . '-' . $u["password"] );

        $query->execute( array( ":password" => $pass, ":id" => $u['id'] ) );
    }
}
catch(Exception $ex)
{
    $db->rollback();
    echo "Error in transaction:";
    echo $ex->getMessage();
    exit( 1 );
}

$db->commit();

echo "Update complete.";
?>
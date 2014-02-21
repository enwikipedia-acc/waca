<?php
// run this script to update the user column in the ban table from usernames to user ids.

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} // Web clients die.

ini_set('display_errors', 1);

require_once 'config.inc.php';
require_once 'includes/PdoDatabase.php';

$db = gGetDb( );

if( ! $db->beginTransaction() ) {
  	echo "Error in transaction: Could not start transaction.";
	exit( 1 );  
}

try
{
    $query = $db->prepare( "SELECT DISTINCT user FROM ban;" );
    if( ! $query->execute() ) {
        $db->rollback();
        echo "Error in transaction: Could not get data.";
        print_r( $db->errorInfo() );
        exit( 1 );
    }

    $data = $query->fetchAll(PDO::FETCH_ASSOC);

    $banupd = $db->prepare("UPDATE ban SET user = :user WHERE user = :olduser;");

    foreach($data as $u)
    {
        $user = User::getByUsername($u['user'], $db);
        if($user != false)
        {
            $banupd->execute( array( ":user" => $user->getId(), ":olduser" => $u['user'] ) );
        }
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
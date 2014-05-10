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
    $query = $db->prepare( "SELECT r . *, IF('themself' = SUBSTR(CAST(log_cmt AS CHAR) FROM 1 FOR LOCATE(' to ', log_cmt) - 1), log_user, SUBSTR(CAST(log_cmt AS CHAR) FROM 1 FOR LOCATE(' to ', log_cmt) - 1)) as oldname, SUBSTR(CAST(log_cmt AS CHAR) FROM LOCATE(' to ', log_cmt) + 4) as newname FROM acc_log r where log_action = 'Renamed';" );
    if( ! $query->execute() ) {
        $db->rollback();
        echo "Error in transaction: Could not get data.";
        print_r( $db->errorInfo() );
        exit( 1 );
    }

    $data = $query->fetchAll(PDO::FETCH_ASSOC);

    $banupd = $db->prepare("UPDATE acc_log SET log_cmt = :cmt WHERE log_id = :id;");

    foreach($data as $u)
    {
        $logentry = serialize(array('old' => $u['oldname'], 'new' => $u['newname']));
        
        $banupd->execute( array( ":cmt" => $logentry, ":id" => $u['log_id'] ) );    
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

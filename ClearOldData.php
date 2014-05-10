<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} // Web clients die.
ini_set('display_errors', 1);
require_once 'config.inc.php';
require_once 'includes/PdoDatabase.php';

$db = gGetDb( );

if( $db->beginTransaction() ) {
	$query = $db->prepare( "UPDATE acc_pend SET pend_ip = :ip, pend_proxyip = :proxy, pend_email = :mail, pend_useragent = :agent WHERE pend_date < DATE_SUB(curdate(), INTERVAL :intvl);" );
	$success = $query->execute( array( 
		":ip" => "127.0.0.1",
		":proxy" => null,
		":mail" => "acc@toolserver.org",
		":agent" => "",
		":intvl" => $dataclear_interval,
	) );
	
	if( ! $success ) {
		$db->rollback();
		echo "Error in transaction: Could not clear data.";
		print_r( $db->errorInfo() );
		exit( 1 );
	}
	
	$db->commit();
}

echo "Deletion complete.";

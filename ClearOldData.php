<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
	die();
} // Web clients die.

ini_set('display_errors', 1);

require_once 'config.inc.php';
require_once 'includes/PdoDatabase.php';

$db = gGetDb( );

$db->transactionally(function() use ($db)
{
	global $cDataClearIp, $cDataClearEmail, $dataclear_interval;
    
	$query = $db->prepare("UPDATE request SET ip = :ip, forwardedip = null, email = :mail, useragent = '' WHERE date < DATE_SUB(curdate(), INTERVAL $dataclear_interval) AND status = 'Closed';");
	$success = $query->execute(array( 
		":ip" => $cDataClearIp,
		":mail" => $cDataClearEmail
	));

	if (!$success) {
		throw new TransactionException("Error in transaction: Could not clear data.");
	}

	$query = $db->prepare("DELETE FROM audit WHERE `timestamp` < DATE_SUB(curdate(), INTERVAL $dataclear_interval);");
	$success = $query->execute();

	if (!$success) {
		throw new TransactionException("Error in transaction: Could not clear data.");
	}

	$query = $db->prepare("DELETE FROM ratelimit WHERE `timestamp` < DATE_SUB(curdate(), INTERVAL 1 DAY);");
	$success = $query->execute();

	if (!$success) {
		throw new TransactionException("Error in transaction: Could not clear data.");
	}
});

echo "Deletion complete.";

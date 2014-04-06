<?php

require_once 'config.inc.php';
require_once 'functions.php';

require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php';
require_once 'includes/database.php';
require_once 'includes/request.php';


// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Initialize the class objects.
$request  = new accRequest();
$accbot   = new accbotSend();
$skin     = new skin();
$strings  = new strings();

$antispoofProvider = new $antispoofProviderClass();
   
$database = gGetDb();

$query = "SELECT * FROM request WHERE status = 'Open' AND emailconfirm = 'Confirmed';";

$statement = $database->prepare($query);
$statement->execute();
	
$requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");

foreach($requests as $req)
{
	$req->setDatabase($database);
	$req->isNew = false;
	
	$spoofs = $antispoofProvider->getSpoofs($req->getName());
	
	if($spoofs)
	{
		$req->setStatus("Admin");
		$req->save();
		
		$statement2 = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:request, 'Clean-up script', 'Deferred to admins', CURRENT_TIMESTAMP());");
        $statement2->bindValue(":request", $req->getId());
        $statement2->execute();
	}
}
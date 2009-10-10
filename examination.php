<?php
/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
** SQL ( http://en.wikipedia.org/User:SQL )                 **
** Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
** FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
** Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
** Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
** Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
** OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
** Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
**                                                           **
**************************************************************/

// Get all the classes.
require_once 'config.inc.php';
require_once 'functions.php';
require_once 'examination.inc';
require_once 'includes/offlineMessage.php'; 

/* ACC EXAMINATION INTERFACE
 * 
 * Examines new candidates before being allowed access to the tool, after the T3chl0v3r incident.
 */

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
$offlineMessage = new offlineMessage(false);
$offlineMessage->check();

// Initialize the session data.
session_start();

// retrieve database connections
global $tsSQLlink, $asSQLlink, $toolserver_database;
list($tsSQLlink, $asSQLlink) = getDBconnections();
@ mysql_select_db($toolserver_database, $tsSQLlink);

if(!isset($_SESSION['user']))
{
	die("please log in before attempting to take the quiz.");
}

$out="";

$username = sanitise($_SESSION['user']);

$checkExamStatusQuery = 'SELECT a.`user_examined` AS "Examined" FROM acc_user a WHERE a.`user_name` = "'.$username.'"';
$checkExamStatusResult = mysql_query($checkExamStatusQuery,$tsSQLlink);
$checkExamStatusRow = mysql_fetch_assoc($checkExamStatusResult);
$checkExamStatus = $checkExamStatusRow['Examined'];

if($checkExamStatus != 0 && !isset($_GET['retake']) ) Die("you have already taken this quiz");

$out.= makehead($username).'<div id="content">';

/*
 * Ideas:
 * 
 * 5 scenarios from batch of scenarios
 * randomly chosen
 * presented one at once, given interface very similar to the zoom interface of the tool
 * 
 * table structure:
 CREATE TABLE `p_acc`.`acc_exam` (
  `question_id` INTEGER  NOT NULL AUTO_INCREMENT,
  `question_email` VARCHAR(512)  NOT NULL,
  `question_ip` varchar(255)  NOT NULL,
  `question_name` varchar(512)  NOT NULL,
  `question_cmt` mediumtext  NOT NULL,
  `question_correct_action` varchar(255)  NOT NULL,
  `question_similar_link` integer  DEFAULT NULL,
  PRIMARY KEY (`question_id`)
)
ENGINE = MyISAM;

 *  CREATE TABLE `acc_exam_similar` (
 * `similar_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 * `similar_name` VARCHAR( 512 ) NOT NULL ,
 * `similar_contribs` MEDIUMTEXT NOT NULL ,
 * `similar_log` VARCHAR( 255 ) NOT NULL,
 * ) ENGINE = InnoDB; 
 *  CREATE TABLE `acc_exam_similarlink` (
 * `link_similar_id` INT( 11 ) NOT NULL,
 * `link_question_id` INT (11) NOT NULL,
 * ) ENGINE = InnoDB; 
 * 
 * exam fail => no access
 * exam pass => admin review
 * 
 * pass = 100% of attempted questions.
 */
 
if(isset($_GET['action']))
{
	switch($_GET['action'])
	{
		case "ask":
			$response = isset($_GET['repl']) ? $_GET['repl'] : "";

			$largestQQuery = 'SELECT COUNT(*) FROM acc_exam a;';
			$largestQResult = mysql_query($largestQQuery,$tsSQLlink);
			$largestQRow = mysql_fetch_assoc($largestQResult);
			$largestQ = $largestQRow['COUNT(*)'];
			
			$id = rand(1,$largestQ);
			
			$out .= showQuestion($id,$response);
			
			break;
		case "answer":
			
			break;
		default:
			$out.= showIntro($username);
			break;
	}
}
else
{
	$out.= showIntro($username);
}
echo $out.showfootern();?>
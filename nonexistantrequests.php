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

// taken, or created requests which are not existant

// for taken,
// log_action == "Closed 3"
// for created,
// log_action == "Closed 1"

/*

SELECT acc_pend.pend_id, acc_pend.pend_name, acc_log.log_action
FROM acc_pend
INNER JOIN acc_log ON acc_pend.pend_id = acc_log.log_pend
WHERE acc_pend.pend_status = "Closed"
AND ( acc_log.log_action = "Closed 1"
      OR acc_log.log_action = "Closed 3" )
;

select count(*) as logs, p.pend_name 
from acc_log l 
inner join acc_pend p on p.pend_id = l.log_pend 
where l.log_action = "Closed 3" or l.log_action = "Closed 1" group by l.log_pend having logs > 1 order by logs asc;

api.php?action=query&list=users&ususers=Stwalkerster|Stwalkersock2&usprop=groups|editcount&format=php

*/

// Get all the classes.
require_once 'config.inc.php';
require_once 'functions.php';
require_once 'includes/offlineMessage.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
$offlineMessage = new offlineMessage(false);
$offlineMessage->check();

ifWikiDbDisabledDie();

die('not implemented yet (requests marked "done" or "taken" on the tool, which actually don\'t exist on enwiki)');
?>

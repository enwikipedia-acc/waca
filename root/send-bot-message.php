<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

if (isset($_SERVER['REQUEST_METHOD'])) {
    die("This is not a valid entry method for this script.");
} //Web clients die.

require_once('../config.inc.php');
require_once('../functions.php');
require_once('../includes/accbotSend.php');

$message = $argv[1];
// $formatted = formatforbot($message);
# sendtobot($message);

$botSend = new accbotSend();
$botSend->send($message);

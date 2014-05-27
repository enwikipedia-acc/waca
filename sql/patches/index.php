<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
**                                                                       **
** Code is released under the Public Domain                   			 **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/
// Redirect user away from the current directory.
require_once('../config.inc.php');
global $baseurl;
header("Location: $baseurl/");
die();

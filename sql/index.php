<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

// Redirect user away from the current directory.
require_once('../config.inc.php');
global $baseurl;
header("Location: $baseurl/");
die();

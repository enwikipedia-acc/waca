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

require_once 'config.inc.php';
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php';

// eurgh. yes, this is needed. The internal header does a logged-in user check, which is a database call...
// Also, SmartyInit checks the currently-logged-in user...
if (Offline::isOffline()) {
    echo Offline::getOfflineMessage(true);
    die();
}

if (isset($_GET['internal'])) {
    BootstrapSkin::displayInternalHeader();
    $smarty->assign("mode", "the Wikipedia Account Request Internal System");
    $smarty->assign("content", "privacy/internal.tpl");
    $smarty->display("privacy/container.tpl");
    BootstrapSkin::displayInternalFooter();
} else {
    BootstrapSkin::displayPublicHeader();
    $smarty->assign("mode", "the Wikipedia Account Request System");
    $smarty->assign("content", "privacy/public.tpl");
    $smarty->display("privacy/container.tpl");
    BootstrapSkin::displayPublicFooter();
}

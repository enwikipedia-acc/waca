<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Smarty;
use SmartyException;
use Waca\DataObjects\User;

/**
 * Handles the tool offline messages
 */
class Offline
{
    /**
     * Determines if the tool is offline
     * @return bool
     */
    public static function isOffline()
    {
        global $dontUseDb;

        return (bool)$dontUseDb;
    }

    /**
     * Gets the offline message
     *
     * @param bool        $external
     * @param null|string $message
     *
     * @return string
     * @throws SmartyException
     */
    public static function getOfflineMessage($external, $message = null)
    {
        global $dontUseDbCulprit, $dontUseDbReason, $baseurl;

        $smarty = new Smarty();
        $smarty->assign("baseurl", $baseurl);
        $smarty->assign("resourceCacheEpoch", 0);
        $smarty->assign("alerts", []);
        $smarty->assign("toolversion", Environment::getToolVersion());

        if (!headers_sent()) {
            header("HTTP/1.1 503 Service Unavailable");
        }

        if ($external) {
            return $smarty->fetch("offline/external.tpl");
        }
        else {
            $hideCulprit = true;

            // Use the provided message if possible
            if ($message === null) {
                $hideCulprit = false;
                $message = $dontUseDbReason;
            }

            $smarty->assign("hideCulprit", $hideCulprit);
            $smarty->assign("dontUseDbCulprit", $dontUseDbCulprit);
            $smarty->assign("dontUseDbReason", $message);
            $smarty->assign("alerts", array());
            $smarty->assign('currentUser', User::getCommunity());

            return $smarty->fetch("offline/internal.tpl");
        }
    }
}

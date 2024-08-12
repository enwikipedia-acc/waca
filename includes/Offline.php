<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca;

use Smarty;
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
    public static function isOffline(SiteConfiguration $configuration): bool
    {
        return (bool)$configuration->getOffline()['offline'];
    }

    /**
     * Gets the offline message
     *
     * @throws Smarty\Exception
     */
    public static function getOfflineMessage(bool $external, SiteConfiguration $configuration, ?string $message = null): string
    {
        $baseurl = $configuration->getBaseUrl();
        $culprit = $configuration->getOffline()['culprit'];
        $reason = $configuration->getOffline()['reason'];

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
                $message = $reason;
            }

            $smarty->assign("hideCulprit", $hideCulprit);
            $smarty->assign("dontUseDbCulprit", $culprit);
            $smarty->assign("dontUseDbReason", $message);
            $smarty->assign("alerts", []);
            $smarty->assign('currentUser', User::getCommunity());
            $smarty->assign('skin', 'main');
            $smarty->assign('currentDomain', null);

            return $smarty->fetch("offline/internal.tpl");
        }
    }
}

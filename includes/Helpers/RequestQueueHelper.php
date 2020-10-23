<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\DataObjects\RequestQueue;

class RequestQueueHelper
{
    /**
     * @param RequestQueue $queue
     * @param bool         $enabled
     * @param bool         $default
     * @param bool         $antiSpoof
     * @param bool         $titleBlacklist
     */
    public function configureDefaults(
        RequestQueue $queue,
        bool $enabled,
        bool $default,
        bool $antiSpoof,
        bool $titleBlacklist
    ) {
        // always allow enabling a queue
        if ($enabled) {
            $queue->setEnabled($enabled);
        }

        // only allow other enable-flag changes if we're not a default
        if (!($queue->isDefault() || $queue->isDefaultAntispoof() || $queue->isDefaultTitleBlacklist())) {
            $queue->setEnabled($enabled);
        }

        // only allow enabling the default flags, and only when we're enabled.
        $queue->setDefault(($queue->isDefault() || $default) && $queue->isEnabled());
        $queue->setDefaultAntispoof(($queue->isDefaultAntispoof() || $antiSpoof) && $queue->isEnabled());
        $queue->setDefaultTitleBlacklist(($queue->isDefaultTitleBlacklist() || $titleBlacklist) && $queue->isEnabled());
    }
}
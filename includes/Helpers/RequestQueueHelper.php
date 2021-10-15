<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\RequestQueue;
use Waca\PdoDatabase;

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
        bool $titleBlacklist,
        bool $isTarget
    ) {
        // always allow enabling a queue
        if ($enabled) {
            $queue->setEnabled($enabled);
        }

        // only allow other enable-flag changes if we're not a default
        if (!($queue->isDefault() || $queue->isDefaultAntispoof() || $queue->isDefaultTitleBlacklist() || $isTarget)) {
            $queue->setEnabled($enabled);
        }

        // only allow enabling the default flags, and only when we're enabled.
        $queue->setDefault(($queue->isDefault() || $default) && $queue->isEnabled());
        $queue->setDefaultAntispoof(($queue->isDefaultAntispoof() || $antiSpoof) && $queue->isEnabled());
        $queue->setDefaultTitleBlacklist(($queue->isDefaultTitleBlacklist() || $titleBlacklist) && $queue->isEnabled());
    }

    /**
     * @param RequestQueue $queue
     * @param PdoDatabase  $database
     *
     * @return bool
     */
    public function isEmailTemplateTarget(RequestQueue $queue, PdoDatabase $database): bool
    {
        $isTarget = false;
        /** @var EmailTemplate[] $deferralTemplates */
        $deferralTemplates = EmailTemplate::getAllActiveTemplates('defer', $database);
        foreach ($deferralTemplates as $t) {
            if ($t->getQueue() === $queue->getId()) {
                $isTarget = true;
                break;
            }
        }

        return $isTarget;
    }
}
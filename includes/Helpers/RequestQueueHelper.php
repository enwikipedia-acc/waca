<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\RequestForm;
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
     * Returns true if the specified queue is a target of an enabled email template with a defer action.
     *
     * @param RequestQueue $queue
     * @param PdoDatabase  $database
     *
     * @return bool
     */
    public function isEmailTemplateTarget(RequestQueue $queue, PdoDatabase $database): bool
    {
        $isTarget = false;
        /** @var EmailTemplate[] $deferralTemplates */
        $deferralTemplates = EmailTemplate::getAllActiveTemplates(EmailTemplate::ACTION_DEFER, $database, $queue->getDomain());
        foreach ($deferralTemplates as $t) {
            if ($t->getQueue() === $queue->getId()) {
                $isTarget = true;
                break;
            }
        }

        return $isTarget;
    }

    public function isRequestFormTarget(RequestQueue $queue, PdoDatabase $database): bool
    {
        $isTarget = false;
        $forms = RequestForm::getAllForms($database, 1); // FIXME: domains
        foreach ($forms as $t) {
            if ($t->isEnabled()) {
                if ($t->getOverrideQueue() === $queue->getId()) {
                    $isTarget = true;
                    break;
                }
            }
        }

        return $isTarget;
    }
}
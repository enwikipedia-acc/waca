<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Background\Task;

use Waca\Background\BackgroundTaskBase;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\DataObjects\WelcomeTemplate;
use Waca\Helpers\MediaWikiHelper;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\PreferenceManager;
use Waca\RequestStatus;

class WelcomeUserTask extends BackgroundTaskBase
{
    /** @var Request */
    private $request;

    public function execute()
    {
        $database = $this->getDatabase();
        $this->request = $this->getRequest();
        $user = $this->getTriggerUser();
        //FIXME: domains
        $userPrefs = new PreferenceManager($database, $user->getId(), 1);

        $welcomeTemplate = $userPrefs->getPreference(PreferenceManager::PREF_WELCOMETEMPLATE);

        if ($welcomeTemplate === null) {
            $this->markFailed('Welcome template not specified');

            return;
        }

        /** @var WelcomeTemplate $template */
        $template = WelcomeTemplate::getById($welcomeTemplate, $database);

        if ($template === false) {
            $this->markFailed('Welcome template missing');

            return;
        }

        $oauth = new OAuthUserHelper($user, $database, $this->getOauthProtocolHelper(),
            $this->getSiteConfiguration());
        $mediaWikiHelper = new MediaWikiHelper($oauth, $this->getSiteConfiguration());

        if ($this->request->getStatus() !== RequestStatus::CLOSED) {
            $this->markFailed('Request is currently open');

            return;
        }

        if (!$mediaWikiHelper->checkAccountExists($this->request->getName())) {
            $this->markFailed('Account does not exist!');

            return;
        }

        $this->performWelcome($template, $this->request, $user, $mediaWikiHelper);
        $this->markComplete();
    }

    /**
     * Performs the welcome
     *
     * @param WelcomeTemplate $template
     * @param Request         $request
     * @param User            $user             The user who triggered the job
     * @param MediaWikiHelper $mediaWikiHelper
     */
    private function performWelcome(
        WelcomeTemplate $template,
        Request $request,
        User $user,
        MediaWikiHelper $mediaWikiHelper
    ) {
        $templateText = $template->getBotCodeForWikiSave($request->getName(), $user->getOnWikiName());

        $mediaWikiHelper->addTalkPageMessage($request->getName(), $template->getSectionHeader(), 'Welcoming user created through [[WP:ACC]]', $templateText);
    }

    protected function markFailed($reason = null, bool $acknowledged = false)
    {
        $this->getNotificationHelper()->requestWelcomeFailed($this->request, $this->getTriggerUser());

        parent::markFailed($reason, $acknowledged);
    }
}
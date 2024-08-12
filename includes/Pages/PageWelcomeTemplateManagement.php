<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages;

use Exception;
use Waca\DataObjects\User;
use Waca\DataObjects\WelcomeTemplate;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\Helpers\MediaWikiHelper;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\PreferenceManager;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageWelcomeTemplateManagement extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $database = $this->getDatabase();
        $templateList = WelcomeTemplate::getAll($database, 1); // FIXME: domains
        $preferenceManager = PreferenceManager::getForCurrent($database);

        $this->setHtmlTitle('Welcome Templates');

        $this->assignCSRFToken();

        $user = User::getCurrent($database);

        $currentTemplate = $preferenceManager->getPreference(PreferenceManager::PREF_WELCOMETEMPLATE);
        $this->assign('currentTemplate', $currentTemplate);

        $this->assign('canEdit', $this->barrierTest('edit', $user));
        $this->assign('canAdd', $this->barrierTest('add', $user));
        $this->assign('canSelect', $this->barrierTest('select', $user));

        $this->assign('templateList', $templateList);
        $this->setTemplate('welcome-template/list.tpl');
    }

    /**
     * Handles the requests for selecting a template to use.
     *
     * @throws ApplicationLogicException
     */
    protected function select()
    {
        // get rid of GETs
        if (!WebRequest::wasPosted()) {
            $this->redirect('welcomeTemplates');
        }

        $this->validateCSRFToken();

        $database = $this->getDatabase();
        $user = User::getCurrent($database);
        $preferenceManager = PreferenceManager::getForCurrent($database);

        if (WebRequest::postBoolean('disable')) {
            $preferenceManager->setLocalPreference(PreferenceManager::PREF_WELCOMETEMPLATE, null);

            SessionAlert::success('Disabled automatic user welcoming.');
            $this->redirect('welcomeTemplates');

            return;
        }

        $templateId = WebRequest::postInt('template');
        /** @var false|WelcomeTemplate $template */
        $template = WelcomeTemplate::getById($templateId, $database);

        if ($template === false || $template->isDeleted()) {
            throw new ApplicationLogicException('Unknown template');
        }

        $preferenceManager->setLocalPreference(PreferenceManager::PREF_WELCOMETEMPLATE, $template->getId());

        SessionAlert::success("Updated selected welcome template for automatic welcoming.");

        $this->redirect('welcomeTemplates');
    }

    /**
     * Handles the requests for viewing a template.
     *
     * @throws ApplicationLogicException
     */
    protected function view()
    {
        $this->setHtmlTitle('View Welcome Template');

        $database = $this->getDatabase();

        $templateId = WebRequest::getInt('template');

        /** @var false|WelcomeTemplate $template */
        $template = WelcomeTemplate::getById($templateId, $database);

        if ($template === false) {
            throw new ApplicationLogicException('Cannot find requested template');
        }

        $currentUser = User::getCurrent($database);

        // This includes a section header, because we use the "new section" API call.
        $wikiText = "== " . $template->getSectionHeader() . "==\n" . $template->getBotCodeForWikiSave('Example User', $currentUser->getOnWikiName());

        $oauth = new OAuthUserHelper($currentUser, $database, $this->getOauthProtocolHelper(),
            $this->getSiteConfiguration());
        $mediaWikiHelper = new MediaWikiHelper($oauth, $this->getSiteConfiguration());

        $templateHtml = $mediaWikiHelper->getHtmlForWikiText($wikiText);
        
        // Add site to relevant links, since the MediaWiki parser returns, eg, `/wiki/Help:Introduction`
        // and we want to link to <https://en.wikipedia.org/wiki/Help:Introduction> rather than
        // <https://accounts.wmflabs.org/wiki/Help:Introduction>
        // The code currently assumes that the template was parsed for enwiki, and will need to be
        // updated once other wikis are supported.
        $templateHtml = preg_replace('/(<a href=")(\/wiki\/)/', '$1//en.wikipedia.org$2', $templateHtml);

        $this->assign('templateHtml', $templateHtml);
        $this->assign('template', $template);
        $this->setTemplate('welcome-template/view.tpl');
    }

    /**
     * Handler for the add action to create a new welcome template
     *
     * @throws Exception
     */
    protected function add()
    {
        $this->assign('createmode', true);

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $database = $this->getDatabase();

            $userCode = WebRequest::postString('usercode');
            $botCode = WebRequest::postString('botcode');

            $this->validate($userCode, $botCode);

            $template = new WelcomeTemplate();
            $template->setDatabase($database);
            $template->setUserCode($userCode);
            $template->setBotCode($botCode);
            $template->setDomain(1); // FIXME: domains!
            $template->save();

            Logger::welcomeTemplateCreated($database, $template);

            $this->getNotificationHelper()->welcomeTemplateCreated($template);

            SessionAlert::success("Template successfully created.");

            $this->redirect('welcomeTemplates');
        }
        else {
            $this->assignCSRFToken();
            $this->assign('template', new WelcomeTemplate());
            $this->setTemplate("welcome-template/edit.tpl");
        }
    }

    /**
     * Handler for editing templates
     */
    protected function edit()
    {
        $database = $this->getDatabase();

        $templateId = WebRequest::getInt('template');

        /** @var false|WelcomeTemplate $template */
        $template = WelcomeTemplate::getById($templateId, $database);

        if ($template === false) {
            throw new ApplicationLogicException('Cannot find requested template');
        }

        if ($template->isDeleted()) {
            throw new ApplicationLogicException('The specified template has been deleted');
        }

        $this->assign('createmode', false);

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $userCode = WebRequest::postString('usercode');
            $botCode = WebRequest::postString('botcode');

            $this->validate($userCode, $botCode);

            $template->setUserCode($userCode);
            $template->setBotCode($botCode);
            $template->setUpdateVersion(WebRequest::postInt('updateversion'));
            $template->save();

            Logger::welcomeTemplateEdited($database, $template);

            SessionAlert::success("Template updated.");

            $this->getNotificationHelper()->welcomeTemplateEdited($template);

            $this->redirect('welcomeTemplates');
        }
        else {
            $this->assignCSRFToken();
            $this->assign('template', $template);
            $this->setTemplate('welcome-template/edit.tpl');
        }
    }

    protected function delete()
    {
        if (!WebRequest::wasPosted()) {
            $this->redirect('welcomeTemplates');
            return;
        }

        $this->validateCSRFToken();

        $database = $this->getDatabase();

        $templateId = WebRequest::postInt('template');
        $updateVersion = WebRequest::postInt('updateversion');

        /** @var false|WelcomeTemplate $template */
        $template = WelcomeTemplate::getById($templateId, $database);

        if ($template === false || $template->isDeleted()) {
            throw new ApplicationLogicException('Cannot find requested template');
        }

        // set the update version to the version sent by the client (optimisticly lock from initial page load)
        $template->setUpdateVersion($updateVersion);

        $database
            ->prepare("UPDATE userpreference SET value = NULL, updateversion = updateversion + 1 WHERE preference = :pref and value = :id;")
            ->execute([
                ':id'   => $templateId,
                ':pref' => PreferenceManager::PREF_WELCOMETEMPLATE
            ]);

        Logger::welcomeTemplateDeleted($database, $template);

        $template->delete();

        $this->redirect('welcomeTemplates');

        SessionAlert::success(
            "Template deleted. Any users who were using this template have had automatic welcoming disabled.");
        $this->getNotificationHelper()->welcomeTemplateDeleted($templateId);
    }

    private function validate($userCode, $botCode)
    {
        if ($userCode === null) {
            throw new ApplicationLogicException('User code cannot be null');
        }

        if ($botCode === null) {
            throw new ApplicationLogicException('Bot code cannot be null');
        }
    }
}

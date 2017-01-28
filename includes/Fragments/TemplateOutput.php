<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Fragments;

use Smarty;
use Waca\DataObjects\User;
use Waca\Environment;
use Waca\SiteConfiguration;

trait TemplateOutput
{
    /** @var Smarty */
    private $smarty;
    /** @var string Extra JavaScript to include at the end of the page's execution */
    private $tailScript;

    /**
     * @return SiteConfiguration
     */
    protected abstract function getSiteConfiguration();

    /**
     * Include extra JavaScript at the end of the page's execution
     *
     * @param $script string JavaScript to include at the end of the page
     */
    final protected function setTailScript($script)
    {
        $this->tailScript = $script;
    }

    /**
     * Assigns a Smarty variable
     *
     * @param  array|string $name  the template variable name(s)
     * @param  mixed        $value the value to assign
     */
    final protected function assign($name, $value)
    {
        $this->smarty->assign($name, $value);
    }

    /**
     * Sets up the variables used by the main Smarty base template.
     *
     * This list is getting kinda long.
     */
    final protected function setUpSmarty()
    {
        $this->smarty = new Smarty();
        $this->smarty->addPluginsDir($this->getSiteConfiguration()->getFilePath() . '/smarty-plugins');

        $this->assign('currentUser', User::getCommunity());
        $this->assign('loggedIn', false);
        $this->assign('baseurl', $this->getSiteConfiguration()->getBaseUrl());
        $this->assign('mediawikiScriptPath', $this->getSiteConfiguration()->getMediawikiScriptPath());

        $this->assign('siteNoticeText', '');
        $this->assign('toolversion', Environment::getToolVersion());

        // default these
        $this->assign('onlineusers', array());
        $this->assign('typeAheadBlock', '');
        $this->assign('extraJs', array());
        $this->assign('extraCss', array());

        // nav menu access control
        $this->assign('nav__canRequests', false);
        $this->assign('nav__canLogs', false);
        $this->assign('nav__canUsers', false);
        $this->assign('nav__canSearch', false);
        $this->assign('nav__canStats', false);
        $this->assign('nav__canBan', false);
        $this->assign('nav__canEmailMgmt', false);
        $this->assign('nav__canWelcomeMgmt', false);
        $this->assign('nav__canSiteNoticeMgmt', false);
        $this->assign('nav__canUserMgmt', false);
        $this->assign('nav__canViewRequest', false);

        $this->assign('page', $this);
    }

    /**
     * Fetches a rendered Smarty template
     *
     * @param $template string Template file path, relative to /templates/
     *
     * @return string Templated HTML
     */
    final protected function fetchTemplate($template)
    {
        $this->assign("tailScript", $this->tailScript);

        return $this->smarty->fetch($template);
    }
}

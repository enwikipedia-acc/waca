<?php

namespace Waca\Fragments;

use InterfaceMessage;
use Smarty;
use User;
use Waca\Environment;

trait TemplateOutput
{
	/** @var Smarty */
	private $smarty;

	/** @var string Extra JavaScript to include at the end of the page's execution */
	private $tailScript;

	/**
	 * Include extra JavaScript at the end of the page's execution
	 *
	 * @param $script string JavaScript to include at the end of the page
	 */
	protected final function setTailScript($script)
	{
		$this->tailScript = $script;
	}

	/**
	 * Assigns a Smarty variable
	 *
	 * @param  array|string $name    the template variable name(s)
	 * @param  mixed        $value   the value to assign
	 */
	protected final function assign($name, $value)
	{
		$this->smarty->assign($name, $value);
	}
	/**
	 * Sets up the variables used by the main Smarty base template.
	 *
	 * This list is getting kinda long.
	 */
	protected final function setUpSmarty()
	{
		global $baseurl, $wikiurl, $mediawikiScriptPath;

		$this->smarty = new Smarty();

		$this->assign("currentUser", User::getCurrent());
		$this->assign("loggedIn", (!User::getCurrent()->isCommunityUser()));
		$this->assign("baseurl", $baseurl);
		$this->assign("wikiurl", $wikiurl);
		$this->assign("mediawikiScriptPath", $mediawikiScriptPath);

		// TODO: this isn't very mockable, and requires a database link.
		$siteNoticeText = InterfaceMessage::get(InterfaceMessage::SITENOTICE);
		$this->assign("siteNoticeText", $siteNoticeText);

		// TODO: this isn't mockable either, and has side effects if you don't have git
		$this->assign("toolversion", Environment::getToolVersion());

		// TODO: implement this somehow
		$this->assign("onlineusers", "");
		$this->assign("tailscript", $this->tailScript);
	}

	/**
	 * Fetches a rendered Smarty template
	 *
	 * @param $template string Template file path, relative to /templates/
	 * @return string Templated HTML
	 */
	protected final function fetchTemplate($template)
	{
		return $this->smarty->fetch($template);
	}
}
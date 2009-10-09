<?php

/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
** SQL ( http://en.wikipedia.org/User:SQL )                 **
** Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
** FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
** Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
** Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
** Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
** OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
** Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
** Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**                                                           **
**************************************************************/

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

class offlineMessage {
	private $dontUseDb;
	private $isExternal;
	
	public function __construct($isExternal) {
		global $dontUseDb;
		$this->dontUseDb = $dontUseDb;
		$this->isExternal = $isExternal;
	}
	
	private function showOfflineMessageHeader() {
		echo <<<HTML
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
		<head>
			<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
			<title>Account Creation Assistance for the English Wikipedia - http://en.wikipedia.org/wiki/Wikipedia:Request an account</title>
			<style type="text/css" media="screen">
				@import "style.css";
			</style>
		</head>

		<body id="body">
			<div id="header">
				<div id="header-title">
					Account Creation Assistance
				</div>
			</div>
			<div id="navigation">
				<a href="http://en.wikipedia.org">English Wikipedia</a> 
			</div>

			<div id="content">
HTML;
	}

	private function showOfflineMessageFooter() {
		echo <<<HTML
			</div>
			<div id="footer">
				Account Creation Assistance Manager by <a href="http://stable.toolserver.org/acc/team.php">The ACC dev team</a>. <a href="https://jira.toolserver.org/browse/ACC">Bugs?</a><br />

				Designed by <a href="http://charlie.mudoo.net/">Charlie Melbye</a>
			</div>
		</body>
	</html>
HTML;
	}

	private function showExternalOfflineMessage() {
		echo <<<HTML
		<h1>Request an account on the English Wikipedia</h1> 		
		<h2>Our apologies!</h2>
		<p>We’re very sorry, but the account creation request tool is currently offline while critical maintenance is performed. We will restore normal operations as soon as possible.</p>
		<p>However, you can still request an account by emailing <a href="mailto:accounts-enwiki-l@lists.wikimedia.org">accounts-enwiki-l@lists.wikimedia.org</a>, with the username that you would like. We’ll take care of your request as soon as possible.</p>
		<p>Thanks for your interest in joining Wikipedia.</p>
HTML;
	}
	
	private function showInternalOfflineMessage() {
		global $offlineProblem, $offlineCulprit;
		echo <<<HTML
		<h2>Whoops!</h2>
		<p>After much experimentation, someone finally managed to kill ACC. So, the tool is currently offline while our resident developers pound their skulls against the furniture.</p> 
		<p>Apparently, this is supposed to fix it.</p>
		<p>Once the nature of the problem is known, we will insert it here: <b>$offlineProblem</b></p>
		<p>Once the identity of the culprit(s) is known, trout should be applied here: <b>$offlineCulprit</b></p>
		<p>Although the tool is dead and the Bot is sleeping, email still works fine. So, we expect a swarm of irate potential editors to bury us in requests shortly. Please keep an eye on the mailing list. Remember to 'cc' or 'bcc' accounts-enwiki when you reply to let others know you have replied.</p> 
		<p>For more information, <a href="irc://irc.freenode.net/#wikipedia-en-accounts">join IRC</a>, check the mailing list (<a href="https://lists.wikimedia.org/mailman/listinfo/accounts-enwiki-l">sign up if you need to</a>) or just light candles – they may help too.</p>
HTML;
}
	
	public function check() {
		if ($this->dontUseDb) {
			$this->showOfflineMessageHeader();
			if ($this->isExternal) {
				$this->showExternalOfflineMessage();
			} else {
				$this->showInternalOfflineMessage();
			}
			$this->showOfflineMessageFooter();
			die();
		}
	}
}
?>

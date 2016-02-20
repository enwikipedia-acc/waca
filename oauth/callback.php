<?php
namespace Waca;

/*
 * OAuth callback script
 * THIS IS AN ENTRY POINT
 *
 * This is hack so we don't have to change the OAuth consumer on-wiki
 * DON'T DO THIS ELSEWHERE.
 *
 * No, really, don't.
 *
 * @todo
 * When you finally get around to binning this file, and I'm guessing it's some point after 2018, you really should make
 * RequestRouter sealed again, and remove the get/set pair from WebStart. Nothing else should use it. If it does, it
 * also needs to be binned.
 *
 * Yes, that was a subtle (as a sledgehammer) reference to xkcd.com/1421
 *
 * Note that binning this file will mean creating a new OAuth consumer, which will invalidate *everyone's* OAuth
 * credentials, so you'll probably want to be sneaky and push them back down to non-attached users unless the far-flung
 * future has some fancy new toys to handle this.
 */

// Change directory so we assume we're in the right place.
chdir("..");

require_once('config.inc.php');

global $siteConfiguration;
$application = new WebStart($siteConfiguration);

// Override the routing algorithm. This is also a hack to force this to be routed where we want it to go.
$application->setRequestRouter(new OAuthRequestRouter());

$application->run();

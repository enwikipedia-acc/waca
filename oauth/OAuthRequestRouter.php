<?php

namespace Waca;

use Waca\Pages\PageOAuth;

/**
 * Class OAuthRequestRouter
 *
 * This is a hack for the OAuth callback script. You almost certainly don't want to use this.
 *
 * And, by the way, don't use this pattern elsewhere. See callback.php for a heads-up about this.
 *
 * @package Waca
 */
class OAuthRequestRouter extends RequestRouter
{
	function getRouteFromPath($pathInfo)
	{
		// Hardcode the route for this entry point
		return array(PageOAuth::class, 'callback');
	}
}
<?php

namespace Waca\Router;

use Waca\Pages\PageOAuth;

/**
 * Class OAuthRequestRouter
 *
 * @package Waca\Router
 */
class OAuthRequestRouter extends RequestRouter
{
	function getRouteFromPath($pathInfo)
	{
		// Hardcode the route for this entry point
		return array(PageOAuth::class, 'callback');
	}
}
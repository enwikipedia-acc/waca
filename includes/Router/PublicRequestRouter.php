<?php

namespace Waca\Router;

use Waca\Pages\Request\PageConfirmEmail;
use Waca\Pages\Request\PageEmailConfirmationRequired;
use Waca\Pages\Request\PageRequestAccount;
use Waca\Pages\Request\PageRequestSubmitted;

class PublicRequestRouter extends RequestRouter
{
	/**
	 * Gets the route map to be used by this request router.
	 *
	 * @return array
	 */
	protected function getRouteMap()
	{
		return array(
			// Page showing a message stating the request has been submitted to our internal queues
			'requestSubmitted'          =>
				array(
					'class'   => PageRequestSubmitted::class,
					'actions' => array(),
				),
			// Page showing a message stating that email confirmation is required to continue
			'emailConfirmationRequired' =>
				array(
					'class'   => PageEmailConfirmationRequired::class,
					'actions' => array(),
				),
			// Action page which handles email confirmation
			'confirmEmail'              =>
				array(
					'class'   => PageConfirmEmail::class,
					'actions' => array(),
				),
		);
	}

	/**
	 * Gets the default route if no explicit route is requested.
	 *
	 * @return callable
	 */
	protected function getDefaultRoute()
	{
		return array(PageRequestAccount::class, 'main');
	}
}
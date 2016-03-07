<?php

namespace Waca\Providers;

use Waca\Providers\Interfaces\ILocationProvider;

/**
 * IP Info DB IP location provider
 */
class IpInfoDbProxyLocationProvider extends IpLocationProvider implements ILocationProvider
{
	protected function getApiBase()
	{
		return "http://api.ipinfodb.com/v3/ip-city/";
	}
}

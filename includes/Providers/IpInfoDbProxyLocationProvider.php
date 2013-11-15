<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class IpInfoDbProxyLocationProvider extends IpLocationProvider implements ILocationProvider
{
    protected function getApiBase()
    {
        return "http://api.ipinfodb.com/v3/ip-city/";
    }
}

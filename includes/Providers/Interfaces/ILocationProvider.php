<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

interface ILocationProvider
{
    public function getIpLocation($address);
}

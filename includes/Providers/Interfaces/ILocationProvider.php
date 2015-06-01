<?php

/**
 * IP Location provider interface
 */
interface ILocationProvider
{
	public function getIpLocation($address);
}

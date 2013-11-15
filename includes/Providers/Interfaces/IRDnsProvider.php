<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

interface IRDnsProvider
{
    public function getRdns($address);   
}

<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

interface IAntiSpoofProvider
{
    public function getSpoofs($username);
}

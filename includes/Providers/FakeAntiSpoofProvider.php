<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class FakeAntiSpoofProvider implements IAntiSpoofProvider
{
    public function getSpoofs($username)
    {
        throw new Exception("This function is currently disabled.");
    }
}

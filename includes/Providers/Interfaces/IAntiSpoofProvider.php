<?php

/**
 * AntiSpoof provider interface
 */
interface IAntiSpoofProvider
{
	public function getSpoofs($username);
}

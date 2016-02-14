<?php

/**
 * AntiSpoof provider interface
 */
interface IAntiSpoofProvider
{
	/**
	 * @param string $username
	 *
	 * @return array
	 */
	public function getSpoofs($username);
}

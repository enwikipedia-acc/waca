<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Providers\Interfaces;

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

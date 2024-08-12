<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Providers\Interfaces;

/**
 * IP Location provider interface
 */
interface ILocationProvider
{
    /**
     * @param string $address IP address
     *
     * @return array
     */
    public function getIpLocation($address);
}

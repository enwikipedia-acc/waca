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
 * Reverse DNS provider interface
 */
interface IRDnsProvider
{
    /**
     * Gets the reverse DNS address for an IP
     *
     * @param string $address
     *
     * @return string
     */
    public function getReverseDNS($address);
}

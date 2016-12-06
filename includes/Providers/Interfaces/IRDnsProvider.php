<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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

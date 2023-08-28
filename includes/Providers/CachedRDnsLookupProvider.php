<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Providers;

use Waca\DataObjects\RDnsCache;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IRDnsProvider;

/**
 * Cached rDNS Lookup Provider
 *
 * Provides a service to look up the reverse DNS of an IP address, and caches
 * the result in the database.
 */
class CachedRDnsLookupProvider implements IRDnsProvider
{
    private $database;

    public function __construct(PdoDatabase $database)
    {
        $this->database = $database;
    }

    public function getReverseDNS($address)
    {
        $address = trim($address);

        // lets look in our cache database first.
        $rDns = RDnsCache::getByAddress($address, $this->database);

        if ($rDns instanceof RDnsCache) {
            // touch cache timer
            $rDns->save();

            return $rDns->getData();
        }

        // OK, it's not there, let's do an rDNS lookup.
        $result = @ gethostbyaddr($address);

        if ($result !== false) {
            $rDns = new RDnsCache();
            $rDns->setDatabase($this->database);
            $rDns->setAddress($address);
            $rDns->setData($result);
            $rDns->save();

            return $result;
        }

        return null;
    }
}

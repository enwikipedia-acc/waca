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
        $ptrAddress = inet_pton($address);
        if ($ptrAddress === false) {
            return null; // Invalid IP address
        }

        $reversePointer = implode('.', array_reverse(explode('.', inet_ntop($ptrAddress)))) . '.in-addr.arpa';
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $reversePointer = implode('.', array_reverse(str_split(bin2hex($ptrAddress)))) . '.ip6.arpa';
        }

        $dnsRecords = dns_get_record($reversePointer, DNS_PTR);
        if (!empty($dnsRecords) && isset($dnsRecords[0]['target'])) {
            $result = $dnsRecords[0]['target'];

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

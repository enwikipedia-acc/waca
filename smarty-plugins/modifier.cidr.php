<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

/**
 * A function which takes an IP address (either IPv4 or IPv6) and a CIDR prefix
 * and calculates the first IP in the range.
 *
 * @param string $ipAddress
 * @param ?int    $cidr
 *
 * @return string
 */
function smarty_modifier_cidr(string $ipAddress, ?int $cidr): string {
    if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        if ($cidr === null) {
            $cidr = 32;
        }

        if ($cidr < 0 || $cidr > 32) {
            throw new InvalidArgumentException("Invalid CIDR prefix for IPv4: $cidr");
        }

        $ipLong = ip2long($ipAddress);
        $mask = -1 << (32 - $cidr);
        $network = $ipLong & $mask;

        return long2ip($network);
    }
    elseif (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        if ($cidr === null) {
            $cidr = 64;
        }

        if ($cidr < 0 || $cidr > 128) {
            throw new InvalidArgumentException("Invalid CIDR prefix for IPv6: $cidr");
        }

        $ipBin = inet_pton($ipAddress);
        $prefix = $cidr;
        $bin = '';
        for ($i = 0; $i < strlen($ipBin); $i++) {
            $bits = 8;
            if ($prefix < 8) {
                $bits = $prefix;
            }
            $mask = $bits === 0 ? 0 : (0xFF << (8 - $bits)) & 0xFF;
            $bin .= chr(ord($ipBin[$i]) & $mask);
            $prefix -= $bits;
            if ($prefix <= 0) {
                $prefix = 0;
            }
        }

        return inet_ntop($bin);
    }

    throw new InvalidArgumentException("Invalid IP address: $ipAddress");
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

/**
 * @param string $input IP address
 *
 * @return string Hex representation of IP.
 */
function smarty_modifier_iphex($input)
{
    $output = $input;

    if (filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
        $octets = explode('.', $input);
        $output = '';
        foreach ($octets as $octet) {
            $output .= str_pad(dechex($octet), 2, '0', STR_PAD_LEFT);
        }

        $output = str_pad($output, 32, '0', STR_PAD_LEFT);
    }

    return $output;
}

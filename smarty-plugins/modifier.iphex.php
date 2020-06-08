<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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

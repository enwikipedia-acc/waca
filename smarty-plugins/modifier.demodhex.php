<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**
 * @param string $input
 *
 * @return int
 */
function smarty_modifier_demodhex($input)
{
    $hex = preg_replace(
        array('/c/', '/b/', '/d/', '/e/', '/f/', '/g/', '/h/', '/i/', '/j/', '/k/', '/l/', '/n/', '/r/', '/t/', '/u/', '/v/'),
        array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'),
        $input);

    return hexdec($hex);
}
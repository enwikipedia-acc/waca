<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 *****************************************************************************
 *
 * @param array  $list
 * @param string $conjunction
 *
 * @return string
 */

function smarty_modifier_nlimplode($list, $conjunction = 'or')
{
    $last = array_pop($list);
    if ($list) {
        return implode(', ', $list) . ', ' . $conjunction . ' ' . $last;
    }
    return $last;
}
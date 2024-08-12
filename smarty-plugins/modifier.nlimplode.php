<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

/*
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
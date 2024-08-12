<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

/**
 * Sets up the cookie-based default sorting on request tables
 *
 * @param                          $params
 * @param Smarty\Template          $template
 *
 * @return string
 */
function smarty_function_defaultsort($params, Smarty\Template $template)
{
    if (empty($params['id'])) {
        return "";
    }

    $attr = 'data-sortname="' . htmlspecialchars($params['id'], ENT_QUOTES) . '"';

    if (empty($params['req'])) {
        return $attr;
    }

    if ($params['dir'] !== 'asc' && $params['dir'] !== 'desc') {
        $params['dir'] = 'asc';
    }

    $sort = '';
    if ($params['req'] === $params['id']) {
        $sort = ' data-defaultsort="' . htmlspecialchars($params['dir'], ENT_QUOTES) . '"';
    }

    return $attr . $sort;
}
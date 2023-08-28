<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

/**
 * Transforms an integer number of seconds in to human-readable timespan
 *
 * @param int $input Number of seconds
 *
 * @return string
 * @example {$variable|timespan} from Smarty
 */
function smarty_modifier_timespan($input)
{
    $remaining = abs(floor($input));

    $seconds = $remaining % 60;
    $remaining = $remaining - $seconds;

    $minutes = $remaining % (60 * 60);
    $remaining = $remaining - $minutes;
    $minutes /= 60;

    $hours = $remaining % (60 * 60 * 24);
    $remaining = $remaining - $hours;
    $hours /= (60 * 60);

    $days = $remaining % (60 * 60 * 24 * 7);
    $weeks = $remaining - $days;
    $days /= (60 * 60 * 24);
    $weeks /= (60 * 60 * 24 * 7);

    $stringval = '';
    $trip = false;

    if ($weeks > 0) {
        $stringval .= "${weeks}w ";
    }

    if ($days > 0) {
        if ($stringval !== '') {
            $trip = true;
        }

        $stringval .= "${days}d ";

        if ($trip) {
            return trim($stringval);
        }
    }

    if ($hours > 0) {
        if ($stringval !== '') {
            $trip = true;
        }

        $stringval .= "${hours}h ";

        if ($trip) {
            return trim($stringval);
        }
    }

    if ($minutes > 0) {
        if ($stringval !== '') {
            $trip = true;
        }

        $stringval .= "${minutes}m ";

        if ($trip) {
            return trim($stringval);
        }
    }

    if ($seconds > 0) {
        if ($stringval !== '') {
            $trip = true;
        }

        $stringval .= "${seconds}s ";

        if ($trip) {
            return trim($stringval);
        }
    }

    return trim($stringval);
}
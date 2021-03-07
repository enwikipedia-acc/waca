<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**
 * Transforms a date string into a relative representation of the date ("2 weeks ago").
 *
 * @param string $input A string representing a date
 *
 * @return string
 * @example {$variable|relativedate} from Smarty
 */
function smarty_modifier_relativedate($input)
{
    $now = new DateTime();

    if (gettype($input) === 'object'
        && (get_class($input) === DateTime::class || get_class($input) === DateTimeImmutable::class)
    ) {
        $then = $input;
    }
    else {
        try {
            $then = new DateTime($input);
        }
        catch (Exception $ex) {
            return $input;
        }
    }

    $secs = $now->getTimestamp() - $then->getTimestamp();

    $second = 1;
    $minute = 60 * $second;
    $minuteCut = 60 * $second;
    $hour = 60 * $minute;
    $hourCut = 90 * $minute;
    $day = 24 * $hour;
    $dayCut = 48 * $hour;
    $week = 7 * $day;
    $weekCut = 14 * $day;
    $month = 30 * $day;
    $monthCut = 60 * $day;
    $year = 365 * $day;
    $yearCut = $year * 2;

    $pluralise = true;

    if ($secs <= 10) {
        $output = "just now";
        $pluralise = false;
    }
    elseif ($secs > 10 && $secs < $minuteCut) {
        $output = round($secs / $second) . " second";
    }
    elseif ($secs >= $minuteCut && $secs < $hourCut) {
        $output = round($secs / $minute) . " minute";
    }
    elseif ($secs >= $hourCut && $secs < $dayCut) {
        $output = round($secs / $hour) . " hour";
    }
    elseif ($secs >= $dayCut && $secs < $weekCut) {
        $output = round($secs / $day) . " day";
    }
    elseif ($secs >= $weekCut && $secs < $monthCut) {
        $output = round($secs / $week) . " week";
    }
    elseif ($secs >= $monthCut && $secs < $yearCut) {
        $output = round($secs / $month) . " month";
    }
    elseif ($secs >= $yearCut && $secs < $year * 10) {
        $output = round($secs / $year) . " year";
    }
    else {
        $output = "a long time ago";
        $pluralise = false;
    }

    if ($pluralise) {
        $output = (substr($output, 0, 2) <> "1 ") ? $output . "s ago" : $output . " ago";
    }

    return $output;
}

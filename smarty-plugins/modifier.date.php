<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**
 * Transforms a date object into a string representation
 *
 * @param DateTime|DateTimeImmutable $input A date
 *
 * @return string
 * @example {$variable|date} from Smarty
 */
function smarty_modifier_date($input)
{
    if (gettype($input) === 'object'
        && (get_class($input) === DateTime::class || get_class($input) === DateTimeImmutable::class)
    ) {
        /** @var $date DateTime|DateTimeImmutable */
        $date = $input;
        $dateString = $date->format('Y-m-d H:i:s');

        return $dateString;
    }
    else {
        return $input;
    }
}
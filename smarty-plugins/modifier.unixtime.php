<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**
 * Transforms a unix timestamp in to a DateTime instance
 *
 * @param DateTime|DateTimeImmutable $input A date
 *
 * @return string
 * @example {$variable|date} from Smarty
 */
function smarty_modifier_unixtime($input)
{
    if (gettype($input) === 'integer') {
        /** @var $date DateTime|DateTimeImmutable */
        $date = DateTimeImmutable::createFromFormat('U', $input);

        return $date;
    }
    else {
        return $input;
    }
}
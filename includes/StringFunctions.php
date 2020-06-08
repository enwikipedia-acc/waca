<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

class StringFunctions
{
    /**
     * Make a string's first character uppercase
     *
     * @param string $string
     *
     * @return string
     */
    public function upperCaseFirst($string)
    {
        if (ord($string) < 128) {
            return ucfirst($string);
        }
        else {
            return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
        }
    }
}

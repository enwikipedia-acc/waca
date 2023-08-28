<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers;

/**
 * DebugHelper provides backtrace utilities for debugging and error logging
 */
class DebugHelper
{
    /**
     * Internal mockable method wrapper for debug_backtrace.
     *
     * As mocking out debug_backtrace uses debug_backtrace internally, we need this in order to not cause a recursive
     * cascade until the runtime explodes.
     *
     * Instead, we mock this method, which allows debug_backtrace to still be called correctly.
     *
     * @return array
     */
    public function get_debug_backtrace()
    {
        return debug_backtrace();
    }

    /**
     * Returns a string representation of the current backtrace for display.
     *
     * Note that this explicitly excludes the top two frames, which will be methods from this class.
     *
     * @return string
     */
    public function getBacktrace()
    {
        $backtrace = $this->get_debug_backtrace();

        $output = "";

        $count = 0;
        foreach ($backtrace as $line) {
            if ($count <= 1) {
                $count++;
                continue;
            }

            $output .= "#{$count}: ";

            if (isset($line['type']) && $line['type'] != "") {
                $output .= $line['class'] . $line['type'];
            }

            $output .= $line['function'] . "(...)";
            $output .= " [{$line['file']}#{$line['line']}\r\n";

            $count++;
        }

        return $output;
    }
}

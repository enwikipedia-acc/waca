<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

/**
 * DebugHelper short summary.
 *
 * DebugHelper description.
 *
 * @version 1.0
 * @author  stwalkerster
 */
class DebugHelper
{
	public function get_debug_backtrace() {
		return debug_backtrace();
	}

	public function getBacktrace()
	{
		$backtrace = $this->get_debug_backtrace();

		$output = "";

		$count = 0;
		foreach ($backtrace as $line) {
			if ($count == 0 || $count == 1) {
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

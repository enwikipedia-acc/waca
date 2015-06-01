<?php

/**
 * DebugHelper short summary.
 *
 * DebugHelper description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class DebugHelper
{
	public static function getBacktrace()
	{
		$backtrace = debug_backtrace();
        
		$output = "";
        
		$count = 0;
		foreach ($backtrace as $line) {
			if ($count == 0) {
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

<?php

/**
 * AutoLoader for the new classes
 */
class AutoLoader
{
	public static function load($class)
	{
		global $filepath;

		// handle namespaces sensibly
		if (strpos($class, "Waca") !== false) {
			// strip off the initial namespace
			$class = str_replace("Waca\\", "", $class);

			// swap backslashes for forward slashes to map to directory names
			$class = str_replace("\\", "/", $class);
		}

		$paths = array(
			$filepath . 'includes/' . $class . ".php",
			$filepath . 'includes/DataObjects/' . $class . ".php",
			$filepath . 'includes/Providers/' . $class . ".php",
			$filepath . 'includes/Providers/Interfaces/' . $class . ".php",
			$filepath . 'includes/Validation/' . $class . ".php",
			$filepath . 'includes/Helpers/' . $class . ".php",
			$filepath . 'includes/Helpers/Interfaces/' . $class . ".php",
			$filepath . $class . ".php",
		);

		foreach ($paths as $file) {
			if (file_exists($file)) {
				require_once($file);
			}

			if (class_exists($class)) {
				return;
			}
		}
	}
}

<?php

/**
 * Class providing information about the tool's runtime environment
 */
class Environment
{
	private static $toolVersion;

	/**
	 * Gets the tool version, using cached data if available.
	 * @return string
	 */
	public static function getToolVersion()
	{
		if (self::$toolVersion == false) {
			self::$toolVersion = exec("git describe --always --dirty");
		}

		return self::$toolVersion;
	}
}

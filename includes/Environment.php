<?php
namespace Waca;

/**
 * Class providing information about the tool's runtime environment
 */
class Environment
{
	/**
	 * @var string Cached copy of the tool version
	 */
	private static $toolVersion = null;

	/**
	 * Gets the tool version, using cached data if available.
	 * @return string
	 */
	public static function getToolVersion()
	{
		if (self::$toolVersion === null) {
			self::$toolVersion = exec("git describe --always --dirty");
		}

		return self::$toolVersion;
	}
}

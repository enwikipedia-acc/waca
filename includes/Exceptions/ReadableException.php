<?php

namespace Waca\Exceptions;

use Exception;
use User;
use Waca\Fragments\TemplateOutput;
use Waca\SiteConfiguration;

/**
 * Class ReadableException
 *
 * Exception which has a readable error message that's displayable to the user using templates.
 *
 * @package Waca\Exceptions
 */
abstract class ReadableException extends Exception
{
	use TemplateOutput;

	/**
	 * Returns a readable HTML error message that's displayable to the user using templates.
	 * @return string
	 */
	abstract public function getReadableError();

	/**
	 * @return SiteConfiguration
	 */
	protected function getSiteConfiguration()
	{
		// Uck. However, we have encountered an exception.
		global $siteConfiguration;
		return $siteConfiguration;
	}
}
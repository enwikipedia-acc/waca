<?php

namespace Waca\Exceptions;

use Exception;
use Waca\Fragments\TemplateOutput;

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
	public abstract function getReadableError();
}
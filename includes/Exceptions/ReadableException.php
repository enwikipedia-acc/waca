<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Exceptions;

use Exception;
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
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

/**
 * Class EnvironmentException
 *
 * To be used when the tool environment does not support running the tool.
 *
 * The main use of this is likely to be when the database fails.
 *
 * @package Waca\Exceptions
 */
class EnvironmentException extends Exception
{
    /**
     * EnvironmentException constructor.
     *
     * @param string $friendlyMessage
     */
    public function __construct($friendlyMessage)
    {
        parent::__construct($friendlyMessage);
    }
}
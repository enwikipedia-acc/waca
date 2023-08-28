<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Providers;

use Waca\Providers\Interfaces\ILocationProvider;

/**
 * Mock IP Location provider for testing and development.
 */
class FakeLocationProvider implements ILocationProvider
{
    public function getIpLocation($address)
    {
        return null;
    }
}

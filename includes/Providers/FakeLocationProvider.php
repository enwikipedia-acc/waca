<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\Interfaces;

use Waca\DataObjects\Ban;

interface IBanHelper
{
    /**
     * Summary of nameIsBanned
     *
     * @param string $name The name to test if is banned.
     *
     * @return Ban
     */
    public function nameIsBanned($name);

    /**
     * Summary of emailIsBanned
     *
     * @param string $email
     *
     * @return Ban
     */
    public function emailIsBanned($email);

    /**
     * Summary of ipIsBanned
     *
     * @param string $ip
     *
     * @return Ban
     */
    public function ipIsBanned($ip);
}

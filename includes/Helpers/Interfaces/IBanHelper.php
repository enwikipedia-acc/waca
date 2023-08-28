<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers\Interfaces;

use Waca\DataObjects\Ban;
use Waca\DataObjects\Request;

interface IBanHelper
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isBlockBanned(Request $request): bool;

    /**
     * @param Request $request
     *
     * @return Ban[]
     */
    public function getBans(Request $request): array;

    public function canUnban(Ban $ban): bool;

    public function isActive(Ban $ban): bool;
}

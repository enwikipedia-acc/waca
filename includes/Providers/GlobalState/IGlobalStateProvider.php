<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Providers\GlobalState;

/**
 * Interface IGlobalStateProvider
 * @package Waca\Providers\Interfaces
 */
interface IGlobalStateProvider
{
    /**
     * @return array
     */
    public function getServerSuperGlobal();

    /**
     * @return array
     */
    public function getGetSuperGlobal();

    /**
     * @return array
     */
    public function getPostSuperGlobal();

    /**
     * @return array
     */
    public function getSessionSuperGlobal();

    /**
     * @return array
     */
    public function getCookieSuperGlobal();
}
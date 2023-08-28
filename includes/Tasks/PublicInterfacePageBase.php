<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tasks;

abstract class PublicInterfacePageBase extends PageBase
{
    /**
     * PublicInterfaceInternalPageBase constructor.
     */
    public function __construct()
    {
        $this->template = 'publicbase.tpl';
    }

    final public function execute()
    {
        parent::execute();
    }

    final public function finalisePage()
    {
        parent::finalisePage();
    }
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\Fragments\PrivacyStatement;
use Waca\Tasks\PublicInterfacePageBase;

class PagePublicPrivacy extends PublicInterfacePageBase
{
    use PrivacyStatement;

    protected function templatePath()
    {
        return 'markdown/public.tpl';
    }
}

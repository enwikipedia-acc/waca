<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\PdoDatabase;

class PageDropRequest extends PageCloseRequest
{
    protected function getTemplate(PdoDatabase $database)
    {
        return EmailTemplate::getDroppedTemplate();
    }

    protected function confirmEmailAlreadySent(Request $request, EmailTemplate $template)
    {
        return false;
    }

    protected function confirmAccountCreated(Request $request, EmailTemplate $template)
    {
        return false;
    }

    protected function sendMail(Request $request, $mailText, User $currentUser, $ccMailingList)
    {
    }
}
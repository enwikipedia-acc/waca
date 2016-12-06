<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Exceptions;

use Waca\DataObjects\User;
use Waca\PdoDatabase;

class NotIdentifiedException extends ReadableException
{
    /**
     * Returns a readable HTML error message that's displayable to the user using templates.
     * @return string
     */
    public function getReadableError()
    {
        if (!headers_sent()) {
            header("HTTP/1.1 403 Forbidden");
        }

        $this->setUpSmarty();

        // uck. We should still be able to access the database in this situation though.
        $database = PdoDatabase::getDatabaseConnection('acc');
        $currentUser = User::getCurrent($database);
        $this->assign('currentUser', $currentUser);
        $this->assign("loggedIn", (!$currentUser->isCommunityUser()));

        return $this->fetchTemplate("exception/not-identified.tpl");
    }
}
<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Waca\Tasks\ConsoleTaskBase;

class ClearOAuthDataTask extends ConsoleTaskBase
{
    public function execute()
    {
        // @fixme this is unsafe.
        // What we should be doing is iterating over all OAuth users, fetching their username, and updating the onwiki
        // name for the user at the same time as blatting out the OAuth credentials, otherwise we risk losing all links
        // to the user's onwiki account.

        $this->getDatabase()->exec(<<<SQL
        UPDATE user
        SET
            oauthrequesttoken = NULL,
            oauthrequestsecret = NULL,
            oauthaccesstoken = NULL,
            oauthaccesssecret = NULL,
            oauthidentitycache = NULL;
SQL
        );
    }
}
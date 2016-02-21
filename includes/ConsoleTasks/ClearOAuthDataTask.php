<?php

namespace Waca\ConsoleTasks;

use Waca\Tasks\ConsoleTaskBase;

class ClearOAuthDataTask extends ConsoleTaskBase
{
	public function execute()
	{
		// @todo this is unsafe.
		// What we should be doing is iterating over all OAuth users, fetching their username, and updating the onwiki
		// name for the user at the same time as blatting out the OAuth credentials, otherwise we risk losing all links
		// to the user's onwiki account.

		$this->getDatabase()->exec(<<<SQL
        UPDATE user
        SET
            oauthrequesttoken = null,
            oauthrequestsecret = null,
            oauthaccesstoken = null,
            oauthaccesssecret = null,
            oauthidentitycache = null;
SQL
		);
	}
}
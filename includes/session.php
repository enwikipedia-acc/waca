<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

class session
{

	public function forceLogout($uid)
	{
		$user = User::getById($uid, gGetDb());
       
		if ($user->getForceLogout() == "1") {
			$_SESSION = array();
			if (isset($_COOKIE[session_name()])) {
				setcookie(session_name(), '', time() - 42000, '/');
			}
			session_destroy( );

			BootstrapSkin::displayInternalHeader();
			
			echo "You have been forcibly logged out, probably due to being renamed. Please log back in.";
            
			BootstrapSkin::displayAlertBox("You have been forcibly logged out, probably due to being renamed. Please log back in.", "alert-error", "Logged out", true, false);
            
			$user->setForceLogout(0);
			$user->save();
            
			BootstrapSkin::displayInternalFooter();
			die();
		}
	}

	/**
	 * Check the user's security level on page load, and bounce accordingly
	 * 
	 * @deprecated
	 */
	public function checksecurity()
	{
		global $secure, $smarty;

		// CommunityUser has no database row, and we really don't want CommunityUser to have oauth credentials...
		if (!User::getCurrent()->isCommunityUser()) {
			if (User::getCurrent()->getStoredOnWikiName() == "##OAUTH##"
				&& User::getCurrent()->getOAuthAccessToken() == null
			) {
				reattachOAuthAccount(User::getCurrent());
			}

			if (User::getCurrent()->isOAuthLinked()) {
				try {
					// test retrieval of the identity
					User::getCurrent()->getOAuthIdentity();
				}
				catch (TransactionException $ex) {
					User::getCurrent()->setOAuthAccessToken(null);
					User::getCurrent()->setOAuthAccessSecret(null);
					User::getCurrent()->save();

					reattachOAuthAccount(User::getCurrent());
				}
			}
			else {
				global $enforceOAuth;

				if ($enforceOAuth) {
					reattachOAuthAccount(User::getCurrent());
				}
			}
		}
        
		if (User::getCurrent()->isNew()) {
			BootstrapSkin::displayAlertBox("I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.", "alert-error", "New account", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}
		elseif (User::getCurrent()->isSuspended()) {
			$database = gGetDb();
			$suspendstatement = $database->prepare(<<<SQL
SELECT comment 
FROM log 
WHERE action = 'Suspended' AND objectid = :userid and objecttype = 'User' 
ORDER BY timestamp DESC
LIMIT 1;
SQL
			);
            
			$suspendstatement->bindValue(":userid", User::getCurrent()->getId());
			$suspendstatement->execute();
            
			$suspendreason = $suspendstatement->fetchColumn();
			$suspendstatement->closeCursor();
            
			$smarty->assign("suspendreason", $suspendreason);
			$smarty->display("login/suspended.tpl");
			BootstrapSkin::displayInternalFooter();
			die();
		}
		elseif (User::getCurrent()->isDeclined()) {
			$database = gGetDb();
			$suspendstatement = $database->prepare(<<<SQL
SELECT comment
FROM log
WHERE action = 'Declined' AND objectid = :userid and objecttype = 'User'
ORDER BY timestamp DESC
LIMIT 1;
SQL
			);
            
			$suspendstatement->bindValue(":userid", User::getCurrent()->getId());
			$suspendstatement->execute();
                
			$suspendreason = $suspendstatement->fetchColumn();
			$suspendstatement->closeCursor();
                
			$smarty->assign("suspendreason", $suspendreason);
			$smarty->display("login/declined.tpl");
			BootstrapSkin::displayInternalFooter();
			die();
		}
		elseif ((!User::getCurrent()->isCommunityUser()) && (User::getCurrent()->isUser() || User::getCurrent()->isAdmin())) {
			$secure = 1;
		}
		else {
			//die("Not logged in!");
		}
	}
}

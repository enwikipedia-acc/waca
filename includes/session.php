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
namespace {

	/**
	 * Class session
	 * @deprecated Legacy class, we should look to move everything out of this.
	 */
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
				session_destroy();

				echo "You have been forcibly logged out, probably due to being renamed. Please log back in.";

				BootstrapSkin::displayAlertBox("You have been forcibly logged out, probably due to being renamed. Please log back in.",
					"alert-error", "Logged out", true, false);

				$user->setForceLogout(0);
				$user->save();

				BootstrapSkin::displayInternalFooter();
				die();
			}
		}
	}
}

namespace Waca {

	/**
	 * Class Session
	 *
	 * This class handles the low-level starting and destroying of sessions.
	 *
	 * @package Waca
	 */
	class Session
	{
		public static function start()
		{
			ini_set('session.cookie_httponly', 1);

			if (WebRequest::isHttps()) {
				ini_set('session.cookie_secure', 1);
			}

			session_start();
		}

		public static function destroy()
		{
			session_destroy();
		}

		public static function restart()
		{
			self::destroy();
			self::start();
		}
	}
}


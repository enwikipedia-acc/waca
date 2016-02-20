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


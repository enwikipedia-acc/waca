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

global $ACC;
global $baseurl;
global $dontUseWikiDb;

if (!defined("ACC")) {
	die();
} // Invalid entry point

require_once 'includes/session.php';

// Initialize the class objects.
$session = new session();

/**
 * Summary of reattachOAuthAccount
 * @param User $user 
 * @throws TransactionException 
 */
function reattachOAuthAccount(User $user)
{
	global $oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal;

	try {
		// Get a request token for OAuth
		$util = new OAuthUtility($oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal);
		$requestToken = $util->getRequestToken();

		// save the request token for later
		$user->setOAuthRequestToken($requestToken->key);
		$user->setOAuthRequestSecret($requestToken->secret);
		$user->save();
            
		$redirectUrl = $util->getAuthoriseUrl($requestToken);
            
		header("Location: {$redirectUrl}");
		die();
	}
	catch (Exception $ex) {
		throw new TransactionException($ex->getMessage(), "Connection to Wikipedia failed.", "alert-error", 0, $ex);
	}     
}

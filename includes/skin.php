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

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

$tagstack = array();

class BootstrapSkin {
    public static function displayPublicHeader() {
        global $smarty;
        $smarty->display("header-external.tpl");
    }
    
    public static function displayInternalHeader() {
        // userid
        // username
        // sitenotice
        global $smarty, $session, $tsSQL;
        
        $userid = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
        $user = isset($_SESSION['user']) ? $_SESSION['user'] : "";
        $msg = new messages();
        $sitenotice = $msg->getSitenotice();
        $smarty->assign("userid", $userid);
        $smarty->assign("username", $user);
        $smarty->assign("sitenotice", $sitenotice);
        $smarty->display("header-internal.tpl");
        //print_r($_SESSION);
        
        if( $userid != 0 ) {
                // Formulates and executes a SQL query to update the last time the user logged in, namely now.
                $now = date("Y-m-d H-i-s"); 
                $query = "UPDATE acc_user SET user_lastactive = '$now' WHERE user_id = '" . sanitize($_SESSION['userID']) . "';";
                $result = $tsSQL->query($query);
        
                $session->forceLogout($_SESSION['userID']);
        }
    }
    
    /**
     * Prints the public interface footer to the screen.
     */
    public static function displayPublicFooter() {
        global $smarty, $tagstack;
        
        // close all declared open tags
        while(count($tagstack) != 0) { echo array_pop($tagstack); }
        
        $online = '';
        $smarty->assign("onlineusers", $online);
        
        $smarty->display("footer.tpl");
    }
    
	/**
     * Prints the internal interface footer to the screen.
     */
    public static function displayInternalFooter() {
        global $smarty, $internalInterface, $tagstack;
        
        // close all declared open tags
        while(count($tagstack) != 0) { echo array_pop($tagstack); }
        
		$howma = $internalInterface->gethowma(true);
		$howout = $internalInterface->showhowma();
		if ($howma != 1) { // not equal to one, as zero uses the plural form too.
			$onlinemessage = "$howma Account Creators currently online (past 5 minutes): $howout";
        } else {
			$onlinemessage = "$howma Account Creator currently online (past 5 minutes): $howout";
        }
        
        $online = '<p class="span6 text-right"><small>' . $onlinemessage . '</small></p>';
        $smarty->assign("onlineusers", $online);
        
        $smarty->display("footer.tpl");
    }
    
    /**
     * Summary of displayAlertBox
     * @param $message
     * @param $type Alert type - use bootstrap css class
     * @param $header
     */
    public static function displayAlertBox( $message, $type = "", $header = "Warning!", $block = true, $closeable = true, $return = false ) {
        global $smarty;
        $smarty->assign("alertmessage", $message);
        $smarty->assign("alerttype", $type);
        $smarty->assign("alertheader", $header);
        $smarty->assign("alertblock", $block);
        $smarty->assign("alertclosable", $closeable);
        if($return) {
            return $smarty->fetch("alert.tpl");
        } else {
            $smarty->display("alert.tpl");
        }
    }
    
	/**
     * Prints the account request form to the screen.
     * @deprecated
     */
    public static function displayRequestForm( ) {
        global $smarty;
        $smarty->display("request-form.tpl");
    }

    public static function pushTagStack($tag) {
        global $tagstack;    
        array_push($tagstack, $tag);
    }
    
    public static function popTagStack() {
        global $tagstack;    
        return array_pop($tagstack);
    }
    
}

/**
 * Old skin stuff, just aliases for BootstrapSkin now
 * @deprecated
 */
class skin {
	
	/**
	 * Prints the public interface header to the screen.
     * @deprecated
	 */
	public function displayPheader() {
		BootstrapSkin::displayPublicHeader();
	}
	
	/**
	 * Prints the internal interface header to the screen.
	 * @param $username The username of the curretly logged in user.
     * @deprecated
	 */
	public function displayIheader($username) {
        BootstrapSkin::displayInternalHeader();
	}
	
	/**
	 * Prints the public interface footer to the screen.
     * @deprecated
	 */
	public function displayPfooter() {
		// Displayes the interface header.
		BootstrapSkin::displayPublicFooter();

		// we probably want to output
		ob_end_flush();
	}
	
	/**
	 * Prints the internal interface footer to the screen.
     * @deprecated
	 */
	public function displayIfooter() {
        BootstrapSkin::displayInternalFooter();
		// we probably want to output
		ob_end_flush();
	}
	
	/**
	 * Prints a request message to the screen.
	 * @param $message The message to print to the screen.
     * @deprecated
	 */
	public function displayRequestMsg($message) {
		// Prints a request message to the screen using the message variable.
		BootstrapSkin::displayAlertBox($message);
	}
	
	/**
     * Prints the account request form to the screen.
     * @deprecated
	 */
	public function displayRequest() {
        BootstrapSkin::displayRequestForm();
	}
}
?>

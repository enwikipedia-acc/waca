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
if (!defined("ACC")) {
	die();
} // Invalid entry point

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
        $sitenotice = InterfaceMessage::get(InterfaceMessage::SITENOTICE);
        $smarty->assign("userid", $userid);
        $smarty->assign("username", $user);
        $smarty->assign("sitenotice", $sitenotice);
        $smarty->assign("alerts", SessionAlert::retrieve());
        $smarty->display("header-internal.tpl");
        //print_r($_SESSION);
        
        if( $userid != 0 ) 
        {
            User::getCurrent()->touchLastLogin();
        
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
        global $smarty, $tagstack;
        
        // close all declared open tags
        while(count($tagstack) != 0) 
        { 
            echo array_pop($tagstack); 
        }
        
		$last5min = time() - 300;
		$last5mins = date("Y-m-d H:i:s", $last5min); 		
		
        $database = gGetDb();
        $statement = $database->prepare("SELECT * FROM user WHERE lastactive > :lastfive;");
        $statement->execute(array(":lastfive" => $last5mins));
        $resultSet = $statement->fetchAll(PDO::FETCH_CLASS, "User");
        $resultSetCount = count($resultSet);
        
        $creators = implode(", ", array_map(function($arg)
        { 
            return "<a href=\"statistics.php?page=Users&amp;user=" . $arg->getId() . "\">" . htmlentities($arg->getUsername()) . "</a>";
        }, $resultSet));
        
		if ($resultSetCount != 1) { // not equal to one, as zero uses the plural form too.
			$onlinemessage = $resultSetCount . " Account Creators currently online (past 5 minutes): $creators";
        } else {
			$onlinemessage = $resultSetCount . " Account Creator currently online (past 5 minutes): $creators";
        }
        
        $online = '<p class="span6 text-right"><small>' . $onlinemessage . '</small></p>';
        
        if( isset( $_SESSION['user'] ) ) 
        {
            $smarty->assign("onlineusers", $online);
        }
        else 
        {
            $emptystring="";
            $smarty->assign("onlineusers", $emptystring);   
        }
        
        $smarty->display("footer.tpl");
    }
    
    /**
     * Summary of displayAlertBox
     * @param $message string Message to show
     * @param $type string Alert type - use bootstrap css class
     * @param $header string the header of the box
     * @param $block bool Whether to make this a block or not
     * @param $closable bool add a close button
     * @param $return bool return the content as a string, or display it.
     * @param $centre bool centre the box in the page, like a dialog.
     */
    public static function displayAlertBox( $message, $type = "", $header = "", $block = false, $closeable = true, $return = false, $centre = false) {
        global $smarty;
        $smarty->assign("alertmessage", $message);
        $smarty->assign("alerttype", $type);
        $smarty->assign("alertheader", $header);
        $smarty->assign("alertblock", $block);
        $smarty->assign("alertclosable", $closeable);
        
        $returnData = $smarty->fetch("alert.tpl");
        
        if($centre)
        {
            $returnData = '<div class="row-fluid"><div class="span8 offset2">' . $returnData . '</div></div>';
        }
        
        if($return) 
        {
            return $returnData;
        } 
        else 
        {
            echo $returnData;
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

    /**
     * @param string $tag
     */
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

<?php

/**
 * Notification short summary.
 *
 * Notification description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class Notification extends DataObject
{
    private $date;
    private $type;
    private $text;
    
    #region database operations
    public function delete()
    {
        throw new Exception("You shouldn't be doing this...");
    }
    
    public function save()
    {        
        if($this->isNew)
		{ // insert
            $statement = $this->dbObject->prepare("INSERT INTO notification ( type, text ) VALUES ( :type, :text );");
            $statement->bindValue(":type", $this->type);
            $statement->bindValue(":text", $this->text);
            
			if($statement->execute())
			{
				$this->isNew = false;
				$this->id = $this->dbObject->lastInsertId();
			}
			else
			{
				throw new Exception($statement->errorInfo());
			}
		}
		else
		{ 
            throw new Exception("You shouldn't be doing this...");
		}         
    }
    #endregion
    
    #region properties
    public function getDate()
    {
		return $this->date;
	}
    
	public function getType()
    {
		return $this->type;
	}
    
    public function getText()
    {
        return $this->text;   
    }

	public function setDate($date)
    {
		$this->date = $date;
	}

	public function setType($type)
    {
		$this->type = $type;
	}

	public function setText($text)
    {
		$this->text = $text;
	}
    #endregion
    
    /**
     * Don't use me directly from outside this class.
     * 
     * There are existing usages on the public interface, but don't add more plz. kthxbai.
     * 
     * @param mixed $message The message to send.
     */
    public static function send($message)
    {
        global $ircBotNotificationType, $whichami;
        
        $blacklist = array("DCC", "CCTP", "PRIVMSG");
		$message = str_replace($blacklist, "(IRC Blacklist)", $message); //Lets stop DCC etc

		$msg = IrcColourCode::RESET . IrcColourCode::BOLD . "[$whichami]". IrcColourCode::RESET .": $message";
        
        $notification = new Notification();
        $notification->setDatabase( gGetDb('notifications') );
        $notification->setType($ircBotNotificationType);
        $notification->setText($msg);
        
        $notification->save();
    }
    
    #region user management
        
    public static function userNew(User $user)
    {
        self::send("New user: {$user->getUsername()}");   
    }
    
    public static function userApproved(User $user)
    {
        self::send("{$user->getUsername()} approved by " . User::getCurrent()->getUsername());   
    }
    
    public static function userPromoted(User $user)
    {
        self::send("{$user->getUsername()} promoted to tool admin by " . User::getCurrent()->getUsername());   
    }
    
    public static function userDeclined(User $user, $reason)
    {
        self::send("{$user->getUsername()} declined by " . User::getCurrent()->getUsername() . " ($reason)");   
    }
    
    public static function userDemoted(User $user, $reason)
    {
        self::send("{$user->getUsername()} demoted by " . User::getCurrent()->getUsername() . " ($reason)");   
    }
    
    public static function userSuspended(User $user, $reason)
    {
        self::send("{$user->getUsername()} suspended by " . User::getCurrent()->getUsername() . " ($reason)");   
    }
    
    public static function userPrefChange(User $user)
    {
        self::send("{$user->getUsername()}'s preferences were changed by " . User::getCurrent()->getUsername());   
    }
    
    public static function userRenamed(User $user, $old)
    {
        self::send(User::getCurrent()->getUsername() . " renamed $old to {$user->getUsername()}");
    }
    
    #endregion
    
    #region Interface Messages
    public static function interfaceMessageEdited(InterfaceMessage $message)
    {
        self::send( "Message {$message->getDescription()} ({$message->getId()}) edited by " . User::getCurrent()->getUsername());
    }
    #endregion
    
    #region Welcome Templates
    public static function welcomeTemplateCreated(WelcomeTemplate $template)
    {
        self::send( "Welcome template {$template->getId()} created by " . User::getCurrent()->getUsername());
    }
    
    public static function welcomeTemplateDeleted($templateid)
    {
        self::send( "Welcome template {$templateid} deleted by " . User::getCurrent()->getUsername());
    }
    
    public static function welcomeTemplateEdited(WelcomeTemplate $template)
    {
        self::send( "Welcome template {$template->getId()} edited by " . User::getCurrent()->getUsername());
    }
    
    #endregion
    
    #region bans
    public static function banned(Ban $ban)
    {
        if($ban->getDuration() == -1)
        {
            $duration = "indefinitely";   
        }
        else
        {
            $duration = "until " . date("F j, Y, g:i a", $ban->getDuration());
        }
        
        self::send( $ban->getTarget() . " banned by " . User::getCurrent()->getUsername() . " for '" . $ban->getReason() . "' " . $duration);
    }
    
    public static function unbanned(Ban $ban, $unbanreason)
    {
        self::send( $ban->getTarget() . " unbanned by " . User::getCurrent()->getUsername() . " (" . $unbanreason . ")");
    }
    
    #endregion
    
    #region request management
    
    public static function requestDeferred(Request $request)
    {
        global $availableRequestStates;
        
        self::send( "Request {$request->getId()} ({$request->getName()}) deferred to {$availableRequestStates[$request->getStatus()]['deferto']} by " . User::getCurrent()->getUsername());
    }
    
    public static function requestClosed(Request $request, $closetype)
    {
        self::send( "Request {$request->getId()} ({$request->getName()}) closed ($closetype) by " . User::getCurrent()->getUsername());
    }
    
    #endregion
    
    #region reservations

    public static function requestReserved(Request $request)
    {
        self::send( "Request {$request->getId()} ({$request->getName()}) reserved by " . User::getCurrent()->getUsername());
    }
    
    public static function requestReserveBroken(Request $request)
    {
        self::send( "Reservation on request {$request->getId()} ({$request->getName()}) broken by " . User::getCurrent()->getUsername());
    }
    
    public static function requestUnreserved(Request $request)
    {
        self::send( "Request {$request->getId()} ({$request->getName()}) is no longer being handled.");
    }
    
    #endregion
    
    #region comments
    
    public static function commentCreated(Comment $comment)
    {
        self::send(User::getCurrent()->getUsername() . " posted a " . ($comment->getVisibility() == "admin" ? "private " : "") . "comment on request {$comment->getRequest()} ({$comment->getRequestObject()->getName()})");
    }

    public static function commentEdited(Comment $comment)
    {
        self::send("Comment {$comment->getId()} on request {$comment->getRequest()} ({$comment->getRequestObject()->getName()}) edited by " . User::getCurrent()->getUsername());
    }

    
    #endregion

    #region email management (close reasons)
    
    public static function emailCreated(EmailTemplate $template)
    {
        self::send( "Email {$template->getId()} ({$template->getName()}) created by " . User::getCurrent()->getUsername());
    }
    
    public static function emailEdited(EmailTemplate $template)
    {
        self::send( "Email {$template->getId()} ({$template->getName()}) edited by " . User::getCurrent()->getUsername());
    }
    
    #endregion
}

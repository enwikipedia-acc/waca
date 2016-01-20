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
		if ($this->isNew) {
			// insert
			$statement = $this->dbObject->prepare("INSERT INTO notification ( type, text ) VALUES ( :type, :text );");
			$statement->bindValue(":type", $this->type);
			$statement->bindValue(":text", $this->text);

			if ($statement->execute()) {
				$this->isNew = false;
				$this->id = $this->dbObject->lastInsertId();
			}
			else {
				throw new Exception($statement->errorInfo());
			}
		}
		else {
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

	/**
	 * Summary of setType
	 * @param int $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * Summary of setText
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}
	#endregion

	/**
	 * Send a notification
	 * @param string $message The text to send
	 */
	protected static function send($message)
	{
		global $ircBotNotificationType, $whichami, $ircBotNotificationsEnabled;

		if (!$ircBotNotificationsEnabled) {
			return;
		}

		$blacklist = array("DCC", "CCTP", "PRIVMSG");
		$message = str_replace($blacklist, "(IRC Blacklist)", $message); //Lets stop DCC etc

		$msg = IrcColourCode::RESET . IrcColourCode::BOLD . "[$whichami]" . IrcColourCode::RESET . ": $message";

		try {
			$database = gGetDb('notifications');
            
			$notification = new Notification();
			$notification->setDatabase($database);
			$notification->setType($ircBotNotificationType);
			$notification->setText($msg);

			$notification->save();
		}
		catch (Exception $ex) {
			// OK, so we failed to send the notification - that db might be down?
			// This is non-critical, so silently fail.
            
			// Disable notifications for remainder of request.
			$ircBotNotificationsEnabled = false;
		}
	}

	#region user management

	/**
	 * send a new user notification
	 * @param User $user
	 */
	public static function userNew(User $user)
	{
		self::send("New user: {$user->getUsername()}");
	}

	/**
	 * send an approved notification
	 * @param User $user
	 */
	public static function userApproved(User $user)
	{
		self::send("{$user->getUsername()} approved by " . User::getCurrent()->getUsername());
	}

	/**
	 * send a promoted notification
	 * @param User $user
	 */
	public static function userPromoted(User $user)
	{
		self::send("{$user->getUsername()} promoted to tool admin by " . User::getCurrent()->getUsername());
	}

	/**
	 * send a declined notification
	 * @param User $user
	 * @param string $reason the reason the user was declined
	 */
	public static function userDeclined(User $user, $reason)
	{
		self::send("{$user->getUsername()} declined by " . User::getCurrent()->getUsername() . " ($reason)");
	}

	/**
	 * send a demotion notification
	 * @param User $user
	 * @param string $reason the reason the user was demoted
	 */
	public static function userDemoted(User $user, $reason)
	{
		self::send("{$user->getUsername()} demoted by " . User::getCurrent()->getUsername() . " ($reason)");
	}

	/**
	 * send a suspended notification
	 * @param User $user
	 * @param string $reason The reason the user has been suspended
	 */
	public static function userSuspended(User $user, $reason)
	{
		self::send("{$user->getUsername()} suspended by " . User::getCurrent()->getUsername() . " ($reason)");
	}

	/**
	 * Send a preference change notification
	 * @param User $user
	 */
	public static function userPrefChange(User $user)
	{
		self::send("{$user->getUsername()}'s preferences were changed by " . User::getCurrent()->getUsername());
	}

	/**
	 * Send a user renamed notification
	 * @param User $user
	 * @param mixed $old
	 */
	public static function userRenamed(User $user, $old)
	{
		self::send(User::getCurrent()->getUsername() . " renamed $old to {$user->getUsername()}");
	}

	#endregion

	#region Interface Messages

	/**
	 * Summary of interfaceMessageEdited
	 * @param InterfaceMessage $message
	 */
	public static function interfaceMessageEdited(InterfaceMessage $message)
	{
		self::send("Message {$message->getDescription()} ({$message->getId()}) edited by " . User::getCurrent()->getUsername());
	}
	#endregion

	#region Welcome Templates
	/**
	 * Summary of welcomeTemplateCreated
	 * @param WelcomeTemplate $template
	 */
	public static function welcomeTemplateCreated(WelcomeTemplate $template)
	{
		self::send("Welcome template {$template->getId()} created by " . User::getCurrent()->getUsername());
	}

	/**
	 * Summary of welcomeTemplateDeleted
	 * @param int $templateid
	 */
	public static function welcomeTemplateDeleted($templateid)
	{
		self::send("Welcome template {$templateid} deleted by " . User::getCurrent()->getUsername());
	}

	/**
	 * Summary of welcomeTemplateEdited
	 * @param WelcomeTemplate $template
	 */
	public static function welcomeTemplateEdited(WelcomeTemplate $template)
	{
		self::send("Welcome template {$template->getId()} edited by " . User::getCurrent()->getUsername());
	}

	#endregion

	#region bans
	/**
	 * Summary of banned
	 * @param Ban $ban
	 */
	public static function banned(Ban $ban)
	{
		if ($ban->getDuration() == -1) {
			$duration = "indefinitely";
		}
		else {
			$duration = "until " . date("F j, Y, g:i a", $ban->getDuration());
		}

		$username = User::getCurrent()->getUsername();

		self::send("{$ban->getTarget()} banned by {$username} for '{$ban->getReason()}' {$duration}");
	}

	/**
	 * Summary of unbanned
	 * @param Ban $ban
	 * @param string $unbanreason
	 */
	public static function unbanned(Ban $ban, $unbanreason)
	{
		self::send($ban->getTarget() . " unbanned by " . User::getCurrent()->getUsername() . " (" . $unbanreason . ")");
	}

	#endregion

	#region request management

	/**
	 * Summary of requestReceived
	 * @param Request $request
	 */
	public static function requestReceived(Request $request)
	{
		global $baseurl;

		self::send(
			IrcColourCode::DARK_GREY . "[["
			. IrcColourCode::DARK_GREEN . "acc:"
			. IrcColourCode::ORANGE . $request->getId()
			. IrcColourCode::DARK_GREY . "]]"
			. IrcColourCode::RED . " N "
			. IrcColourCode::DARK_BLUE . $baseurl . "/acc.php?action=zoom&id={$request->getId()} "
			. IrcColourCode::DARK_RED . "* "
			. IrcColourCode::DARK_GREEN . $request->getName()
			. IrcColourCode::DARK_RED . " * "
			. IrcColourCode::RESET
			);
	}

	/**
	 * Summary of requestDeferred
	 * @param Request $request
	 */
	public static function requestDeferred(Request $request)
	{
		global $availableRequestStates;

		$deferTo = $availableRequestStates[$request->getStatus()]['deferto'];
		$username = User::getCurrent()->getUsername();

		self::send("Request {$request->getId()} ({$request->getName()}) deferred to {$deferTo} by {$username}");
	}
	/**
	 * 
	 * Summary of requestDeferredWithMail
	 * @param Request $request
	 */
	public static function requestDeferredWithMail(Request $request)
	{
		global $availableRequestStates;

		$deferTo = $availableRequestStates[$request->getStatus()]['deferto'];
		$username = User::getCurrent()->getUsername();

		self::send("Request {$request->getId()} ({$request->getName()}) deferred to {$deferTo} with an email by {$username}");
	}

	/**
	 * Summary of requestClosed
	 * @param Request $request
	 * @param string $closetype
	 */
	public static function requestClosed(Request $request, $closetype)
	{
		$username = User::getCurrent()->getUsername();

		self::send("Request {$request->getId()} ({$request->getName()}) closed ($closetype) by {$username}");
	}

	/**
	 * Summary of sentMail
	 * @param Request $request
	 */
	public static function sentMail(Request $request)
	{
		self::send(User::getCurrent()->getUsername() 
			. " sent an email related to Request {$request->getId()} ({$request->getName()})");
	}

	#endregion

	#region reservations

	/**
	 * Summary of requestReserved
	 * @param Request $request
	 */
	public static function requestReserved(Request $request)
	{
		$username = User::getCurrent()->getUsername();

		self::send("Request {$request->getId()} ({$request->getName()}) reserved by {$username}");
	}

	/**
	 * Summary of requestReserveBroken
	 * @param Request $request
	 */
	public static function requestReserveBroken(Request $request)
	{
		$username = User::getCurrent()->getUsername();

		self::send("Reservation on request {$request->getId()} ({$request->getName()}) broken by {$username}");
	}

	/**
	 * Summary of requestUnreserved
	 * @param Request $request
	 */
	public static function requestUnreserved(Request $request)
	{
		self::send("Request {$request->getId()} ({$request->getName()}) is no longer being handled.");
	}

	/**
	 * Summary of requestReservationSent
	 * @param Request $request
	 * @param User $target
	 */
	public static function requestReservationSent(Request $request, User $target)
	{
		$username = User::getCurrent()->getUsername();

		self::send(
			"Reservation of request {$request->getId()} ({$request->getName()}) sent to {$target->getUsername()} by " 
			. $username);
	}

	#endregion

	#region comments

	/**
	 * Summary of commentCreated
	 * @param Comment $comment
	 */
	public static function commentCreated(Comment $comment)
	{
		$req = $comment->getRequestObject();
		$username = User::getCurrent()->getUsername();
		$visibility = ($comment->getVisibility() == "admin" ? "private " : "");

		self::send("{$username} posted a {$visibility}comment on request {$req->getId()} ({$req->getName()})");
	}

	/**
	 * Summary of commentEdited
	 * @param Comment $comment
	 */
	public static function commentEdited(Comment $comment)
	{
		$req = $comment->getRequestObject();
		$username = User::getCurrent()->getUsername();

		self::send("Comment {$comment->getId()} on request {$req->getId()} ({$req->getName()}) edited by {$username}");
	}

	#endregion

	#region email management (close reasons)

	/**
	 * Summary of emailCreated
	 * @param EmailTemplate $template
	 */
	public static function emailCreated(EmailTemplate $template)
	{
		self::send("Email {$template->getId()} ({$template->getName()}) created by " . User::getCurrent()->getUsername());
	}

	/**
	 * Summary of emailEdited
	 * @param EmailTemplate $template
	 */
	public static function emailEdited(EmailTemplate $template)
	{
		self::send("Email {$template->getId()} ({$template->getName()}) edited by " . User::getCurrent()->getUsername());
	}

	#endregion
}

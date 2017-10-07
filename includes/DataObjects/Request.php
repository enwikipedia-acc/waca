<?php

/**
 * Request data object
 *
 * This data object is the main request object.
 */
class Request extends DataObject
{
	private $email;
	private $ip;
	private $name;
	private $comment;
	private $status = "Open";
	private $date;
	private $checksum = 0;
	private $emailsent = 0;
	private $emailconfirm;
	private $reserved = 0;
	private $useragent;
	private $forwardedip;

	private $hasComments = false;
	private $hasCommentsResolved = false;

	/**
	 * @var Request[]
	 */
	private $ipRequests;
	private $ipRequestsResolved = false;

	/**
	 * @var Request[]
	 */
	private $emailRequests;
	private $emailRequestsResolved = false;

	private $blacklistCache = null;

	/**
	 * This function removes all old requests which are not yet email-confirmed
	 * from the database.
	 */
	public static function cleanExpiredUnconfirmedRequests()
	{
		global $emailConfirmationExpiryDays;

		$database = gGetDb();
		$statement = $database->prepare(<<<SQL
            DELETE FROM request
            WHERE
                date < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL $emailConfirmationExpiryDays DAY)
                AND emailconfirm != 'Confirmed'
                AND emailconfirm != '';
SQL
		);

		$statement->execute();
	}

	public function save()
	{
		if ($this->isNew) {
// insert
			$statement = $this->dbObject->prepare(
				"INSERT INTO `request` (" .
				"email, ip, name, comment, status, date, checksum, emailsent, emailconfirm, reserved, useragent, forwardedip" .
				") VALUES (" .
				":email, :ip, :name, :comment, :status, CURRENT_TIMESTAMP(), :checksum, :emailsent," .
				":emailconfirm, :reserved, :useragent, :forwardedip" .
				");");
			$statement->bindValue(":email", $this->email);
			$statement->bindValue(":ip", $this->ip);
			$statement->bindValue(":name", $this->name);
			$statement->bindValue(":comment", $this->comment);
			$statement->bindValue(":status", $this->status);
			$statement->bindValue(":checksum", $this->checksum);
			$statement->bindValue(":emailsent", $this->emailsent);
			$statement->bindValue(":emailconfirm", $this->emailconfirm);
			$statement->bindValue(":reserved", $this->reserved);
			$statement->bindValue(":useragent", $this->useragent);
			$statement->bindValue(":forwardedip", $this->forwardedip);
			if ($statement->execute()) {
				$this->isNew = false;
				$this->id = (int)$this->dbObject->lastInsertId();
			}
			else {
				throw new Exception($statement->errorInfo());
			}
		}
		else {
// update
			$statement = $this->dbObject->prepare("UPDATE `request` SET " .
				"status = :status, checksum = :checksum, emailsent = :emailsent, emailconfirm = :emailconfirm, " .
				"reserved = :reserved " .
				"WHERE id = :id LIMIT 1;");
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":status", $this->status);
			$statement->bindValue(":checksum", $this->checksum);
			$statement->bindValue(":emailsent", $this->emailsent);
			$statement->bindValue(":emailconfirm", $this->emailconfirm);
			$statement->bindValue(":reserved", $this->reserved);
			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		}

	}

	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function getTrustedIp()
	{
		return trim(getTrustedClientIP($this->ip, $this->forwardedip));
	}

	/**
	 * @param string $ip
	 */
	public function setIp($ip)
	{
		$this->ip = $ip;
	}

	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	public function getComment()
	{
		return $this->comment;
	}

	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function setDate($date)
	{
		$this->date = $date;
	}

	public function getChecksum()
	{
		return $this->checksum;
	}

	public function setChecksum($checksum)
	{
		$this->checksum = $checksum;
	}

	public function updateChecksum()
	{
		$this->checksum = md5($this->id . $this->name . $this->email . microtime());
	}

	public function getEmailSent()
	{
		return $this->emailsent;
	}

	public function setEmailSent($emailsent)
	{
		$this->emailsent = $emailsent;
	}

	public function getEmailConfirm()
	{
		return $this->emailconfirm;
	}

	/**
	 * @param string $emailconfirm
	 */
	public function setEmailConfirm($emailconfirm)
	{
		$this->emailconfirm = $emailconfirm;
	}

	public function getReserved()
	{
		return $this->reserved;
	}

	public function getReservedObject()
	{
		return User::getById($this->reserved, $this->dbObject);
	}

	public function setReserved($reserved)
	{
		$this->reserved = $reserved;
	}

	public function getUserAgent()
	{
		return $this->useragent;
	}

	public function setUserAgent($useragent)
	{
		$this->useragent = $useragent;
	}

	public function getForwardedIp()
	{
		return $this->forwardedip;
	}

	public function setForwardedIp($forwardedip)
	{
		// Verify that the XFF chain only contains valid IP addresses, and silently discard anything that isn't.

		$xff = explode(',', $forwardedip);
		$valid = array();

		foreach ($xff as $ip) {
			$ip = trim($ip);
			if (filter_var($ip, FILTER_VALIDATE_IP)) {
				$valid[] = $ip;
			}
		}

		$this->forwardedip = implode(", ", $valid);
	}

	public function hasComments()
	{
		if ($this->hasCommentsResolved) {
			return $this->hasComments;
		}

		if ($this->comment != "") {
			$this->hasComments = true;
			$this->hasCommentsResolved = true;
			return true;
		}

		$commentsQuery = $this->dbObject->prepare("SELECT COUNT(*) as num FROM comment where request = :id;");
		$commentsQuery->bindValue(":id", $this->id);

		$commentsQuery->execute();

		$this->hasComments = ($commentsQuery->fetchColumn() != 0);
		$this->hasCommentsResolved = true;

		return $this->hasComments;
	}

	public function getRelatedEmailRequests()
	{
		if ($this->emailRequestsResolved == false) {
			global $cDataClearEmail;

			$query = $this->dbObject->prepare("SELECT * FROM request WHERE email = :email AND email != :clearedemail AND id != :id AND emailconfirm = 'Confirmed';");
			$query->bindValue(":id", $this->id);
			$query->bindValue(":email", $this->email);
			$query->bindValue(":clearedemail", $cDataClearEmail);

			$query->execute();

			$this->emailRequests = $query->fetchAll(PDO::FETCH_CLASS, "Request");
			$this->emailRequestsResolved = true;

			foreach ($this->emailRequests as $r) {
				$r->setDatabase($this->dbObject);
			}
		}

		return $this->emailRequests;
	}

	public function getRelatedIpRequests()
	{
		if ($this->ipRequestsResolved == false) {
			global $cDataClearIp;

			$query = $this->dbObject->prepare("SELECT * FROM request WHERE (ip = :ip OR forwardedip LIKE :forwarded) AND ip != :clearedip AND id != :id AND emailconfirm = 'Confirmed';");

			$trustedIp = $this->getTrustedIp();
			$trustedFilter = '%' . $trustedIp . '%';

			$query->bindValue(":id", $this->id);
			$query->bindValue(":ip", $trustedIp);
			$query->bindValue(":forwarded", $trustedFilter);
			$query->bindValue(":clearedip", $cDataClearIp);

			$query->execute();

			$this->ipRequests = $query->fetchAll(PDO::FETCH_CLASS, "Request");
			$this->ipRequestsResolved = true;

			foreach ($this->ipRequests as $r) {
				$r->setDatabase($this->dbObject);
			}
		}

		return $this->ipRequests;
	}

	public function isBlacklisted()
	{
		global $enableTitleBlacklist;

		if (!$enableTitleBlacklist || $this->blacklistCache === false) {
			return false;
		}

		$apiResult = file_get_contents("https://en.wikipedia.org/w/api.php?action=titleblacklist&tbtitle=" . urlencode($this->name) . "&tbaction=new-account&tbnooverride&format=php");

		$data = unserialize($apiResult);

		$result = $data['titleblacklist']['result'] == "ok";

		$this->blacklistCache = $result ? false : $data['titleblacklist']['line'];

		return $this->blacklistCache;
	}

	public function getComments()
	{
		return Comment::getForRequest($this->id, $this->dbObject);
	}

	public function isProtected()
	{
		if ($this->reserved != 0) {
			if ($this->reserved == User::getCurrent()->getId()) {
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}

	}

	public function confirmEmail($si)
	{
		if ($this->getEmailConfirm() == "Confirmed") {
			// already confirmed. Act as though we've completed successfully.
			return;
		}

		if ($this->getEmailConfirm() == $si) {
			$this->setEmailConfirm("Confirmed");
		}
		else {
			throw new TransactionException("Confirmation hash does not match the expected value", "Email confirmation failed");
		}
	}

	public function generateEmailConfirmationHash()
	{
		$this->emailconfirm = bin2hex(openssl_random_pseudo_bytes(16));
	}

	public function sendConfirmationEmail()
	{
		global $smarty;

		$smarty->assign("ip", $this->getTrustedIp());
		$smarty->assign("id", $this->getId());
		$smarty->assign("hash", $this->getEmailConfirm());

		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';

		// Sends the confirmation email to the user.
		$mailsuccess = mail($this->getEmail(), "[ACC #{$this->getId()}] English Wikipedia Account Request", $smarty->fetch('request/confirmation-mail.tpl'), $headers);

		if (!$mailsuccess) {
			throw new Exception("Error sending email.");
		}
	}
	
	public function getObjectDescription()
	{
		return '<a href="acc.php?action=zoom&amp;id=' . $this->getId() . '">Request #' . $this->getId() . " (" . htmlentities($this->name) . ")</a>";
	}

	public function getClosureReason()
	{
		if ($this->status != 'Closed') {
			throw new Exception("Can't get closure reason for open request.");
		}

		$statement = $this->dbObject->prepare(<<<SQL
SELECT closes.mail_desc
FROM log
INNER JOIN closes ON log.action = closes.closes
WHERE log.objecttype = 'Request'
AND log.objectid = :requestId
AND log.action LIKE 'Closed%'
ORDER BY log.timestamp DESC
LIMIT 1;
SQL
		);

		$statement->bindValue(":requestId", $this->id);
		$statement->execute();
		$reason = $statement->fetchColumn();

		return $reason;
	}
}

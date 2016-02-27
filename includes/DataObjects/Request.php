<?php
namespace Waca\DataObjects;

use DateTime;
use Exception;
use PDO;
use Waca\DataObject;
use Waca\Providers\Interfaces\IXffTrustProvider;

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
	/** @var string|null */
	private $comment;
	private $status = "Open";
	private $date;
	private $checksum = '0';
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
	 * @throws Exception
	 */
	public function save()
	{
		if ($this->isNew) {
			// insert
			$statement = $this->dbObject->prepare(<<<SQL
INSERT INTO `request` (
	email, ip, name, comment, status, date, checksum, emailsent,
	emailconfirm, reserved, useragent, forwardedip
) VALUES (
	:email, :ip, :name, :comment, :status, CURRENT_TIMESTAMP(), :checksum, :emailsent,
	:emailconfirm, :reserved, :useragent, :forwardedip
);
SQL
			);
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
			$statement = $this->dbObject->prepare(<<<SQL
UPDATE `request` SET
	status = :status,
	checksum = :checksum,
	emailsent = :emailsent,
	emailconfirm = :emailconfirm,
	reserved = :reserved
WHERE id = :id
LIMIT 1;
SQL
			);
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

	/**
	 * @return string
	 */
	public function getIp()
	{
		return $this->ip;
	}

	/**
	 * @param string $ip
	 */
	public function setIp($ip)
	{
		$this->ip = $ip;
	}

	/**
	 * @return string
	 */
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

	/**
	 * @return string|null
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @param string $comment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @return string
	 */
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

	/**
	 * @todo make this support DateTime object
	 * @return string
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @todo make this support DateTime object
	 *
	 * @param string $date
	 */
	public function setDate($date)
	{
		$this->date = $date;
	}

	/**
	 * @return string
	 */
	public function getChecksum()
	{
		return $this->checksum;
	}

	/**
	 * @param string $checksum
	 */
	public function setChecksum($checksum)
	{
		$this->checksum = $checksum;
	}

	/**
	 * @deprecated in favour of optimistic locking IDs
	 */
	public function updateChecksum()
	{
		$this->checksum = md5($this->id . $this->name . $this->email . microtime());
	}

	/**
	 * @todo change this to boolean
	 * @return int
	 */
	public function getEmailSent()
	{
		return $this->emailsent;
	}

	/**
	 * @todo change this to boolean
	 *
	 * @param int $emailsent
	 */
	public function setEmailSent($emailsent)
	{
		$this->emailsent = $emailsent;
	}

	/**
	 * @todo allow this to return null instead
	 * @return int
	 */
	public function getReserved()
	{
		return $this->reserved;
	}

	/**
	 * @param int|null $reserved
	 */
	public function setReserved($reserved)
	{
		if ($reserved === null) {
			// @todo this shouldn't be needed!
			$reserved = 0;
		}

		$this->reserved = $reserved;
	}

	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return $this->useragent;
	}

	/**
	 * @param string $useragent
	 */
	public function setUserAgent($useragent)
	{
		$this->useragent = $useragent;
	}

	/**
	 * @return string|null
	 */
	public function getForwardedIp()
	{
		return $this->forwardedip;
	}

	/**
	 * @param string|null $forwardedip
	 */
	public function setForwardedIp($forwardedip)
	{
		$this->forwardedip = $forwardedip;
	}

	/**
	 * @return bool
	 */
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

		$commentsQuery = $this->dbObject->prepare("SELECT COUNT(*) AS num FROM comment WHERE request = :id;");
		$commentsQuery->bindValue(":id", $this->id);

		$commentsQuery->execute();

		$this->hasComments = ($commentsQuery->fetchColumn() != 0);
		$this->hasCommentsResolved = true;

		return $this->hasComments;
	}

	/**
	 * @deprecated this shouldn't be here.
	 * @return Request[]
	 */
	public function getRelatedEmailRequests()
	{
		if ($this->emailRequestsResolved == false) {
			global $cDataClearEmail;

			$query = $this->dbObject->prepare(<<<SQL
SELECT * FROM request
WHERE email = :email AND email != :clearedemail AND id != :id AND emailconfirm = 'Confirmed';
SQL
			);
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

	/**
	 * @deprecated this shouldn't be here.
	 * @return Request[]
	 */
	public function getRelatedIpRequests()
	{
		if ($this->ipRequestsResolved == false) {
			global $cDataClearIp;

			$query = $this->dbObject->prepare(<<<SQL
SELECT * FROM request
WHERE (ip = :ip OR forwardedip LIKE :forwarded) AND ip != :clearedip AND id != :id AND emailconfirm = 'Confirmed';
SQL
			);

			/**
			 * Note to weary travellers! Don't use this global anywhere else.
			 * @var IXffTrustProvider $globalXffTrustProvider
			 */
			global $globalXffTrustProvider;
			$trustedIp = $globalXffTrustProvider->getTrustedClientIp($this->ip, $this->forwardedip);

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

	/** @deprecated Should be moved to a helper method */
	public function isBlacklisted()
	{
		global $enableTitleBlacklist;

		if (!$enableTitleBlacklist || $this->blacklistCache === false) {
			return false;
		}

		$apiResult = file_get_contents("https://en.wikipedia.org/w/api.php?action=titleblacklist&tbtitle="
			. urlencode($this->name)
			. "&tbaction=new-account&tbnooverride&format=php");

		$data = unserialize($apiResult);

		$result = $data['titleblacklist']['result'] == "ok";

		$this->blacklistCache = $result ? false : $data['titleblacklist']['line'];

		return $this->blacklistCache;
	}

	/**
	 * @return Comment[]
	 * @deprecated This shouldn't be here
	 */
	public function getComments()
	{
		return Comment::getForRequest($this->id, $this->dbObject);
	}

	/**
	 * @param $si
	 *
	 * @deprecated
	 * @throws Exception
	 */
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
			throw new Exception("Confirmation hash does not match the expected value",
				"Email confirmation failed");
		}
	}

	/**
	 * @return string
	 */
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

	public function generateEmailConfirmationHash()
	{
		$this->emailconfirm = bin2hex(openssl_random_pseudo_bytes(16));
	}

	/**
	 * @deprecated Move to helper!
	 */
	public function sendConfirmationEmail()
	{
		global $smarty;

		/**
		 * Note to weary travellers! Don't use this global anywhere else.
		 * @var IXffTrustProvider $globalXffTrustProvider
		 */
		global $globalXffTrustProvider;
		$trustedIp = $globalXffTrustProvider->getTrustedClientIp($this->ip, $this->forwardedip);

		$smarty->assign("ip", $trustedIp);
		$smarty->assign("id", $this->getId());
		$smarty->assign("hash", $this->getEmailConfirm());

		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';

		// Sends the confirmation email to the user.
		$mailsuccess = mail($this->getEmail(), "[ACC #{$this->getId()}] English Wikipedia Account Request",
			$smarty->fetch('request/confirmation-mail.tpl'), $headers);

		if (!$mailsuccess) {
			throw new Exception("Error sending email.");
		}
	}

	/**
	 * @return string|null
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string|null $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getObjectDescription()
	{
		$value = '<a href="internal.php/viewRequest?id=' . $this->getId() . '">Request #' . $this->getId() . " ("
			. htmlentities($this->name) . ")</a>";

		return $value;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
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

	/**
	 * Gets a value indicating whether the request was closed as created or not.
	 */
	public function getWasCreated()
	{
		if ($this->status != 'Closed') {
			throw new Exception("Can't get closure reason for open request.");
		}

		$statement = $this->dbObject->prepare(<<<SQL
SELECT emailtemplate.oncreated, log.action
FROM log
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE log.objecttype = 'Request'
AND log.objectid = :requestId
AND log.action LIKE 'Closed%'
ORDER BY log.timestamp DESC
LIMIT 1;
SQL
		);

		$statement->bindValue(":requestId", $this->id);
		$statement->execute();
		$onCreated = $statement->fetchColumn(0);
		$logAction = $statement->fetchColumn(1);
		$statement->closeCursor();

		if ($onCreated === null) {
			return $logAction === "Closed custom-y";
		}

		return (bool)$onCreated;
	}

	/**
	 * @return DateTime
	 */
	public function getClosureDate()
	{
		$logQuery = $this->dbObject->prepare(<<<SQL
SELECT timestamp FROM log
WHERE objectid = :request AND objecttype = 'Request' AND action LIKE 'Closed%'
ORDER BY timestamp DESC LIMIT 1;
SQL
		);
		$logQuery->bindValue(":request", $this->getId());
		$logQuery->execute();
		$logTime = $logQuery->fetchColumn();
		$logQuery->closeCursor();

		return new DateTime($logTime);
	}
}

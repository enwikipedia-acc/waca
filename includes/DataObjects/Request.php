<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use DateTime;
use DateTimeImmutable;
use Exception;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;

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
    private $status = "Open";
    private $date;
    private $emailsent = 0;
    private $emailconfirm;
    /** @var int|null */
    private $reserved = null;
    private $useragent;
    private $forwardedip;
    private $hasComments = false;
    private $hasCommentsResolved = false;

    /**
     * @throws Exception
     * @throws OptimisticLockFailedException
     */
    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
INSERT INTO `request` (
	email, ip, name, status, date, emailsent,
	emailconfirm, reserved, useragent, forwardedip
) VALUES (
	:email, :ip, :name, :status, CURRENT_TIMESTAMP(), :emailsent,
	:emailconfirm, :reserved, :useragent, :forwardedip
);
SQL
            );
            $statement->bindValue(':email', $this->email);
            $statement->bindValue(':ip', $this->ip);
            $statement->bindValue(':name', $this->name);
            $statement->bindValue(':status', $this->status);
            $statement->bindValue(':emailsent', $this->emailsent);
            $statement->bindValue(':emailconfirm', $this->emailconfirm);
            $statement->bindValue(':reserved', $this->reserved);
            $statement->bindValue(':useragent', $this->useragent);
            $statement->bindValue(':forwardedip', $this->forwardedip);

            if ($statement->execute()) {
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
	emailsent = :emailsent,
	emailconfirm = :emailconfirm,
	reserved = :reserved,
	updateversion = updateversion + 1
WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(':status', $this->status);
            $statement->bindValue(':emailsent', $this->emailsent);
            $statement->bindValue(':emailconfirm', $this->emailconfirm);
            $statement->bindValue(':reserved', $this->reserved);

            if (!$statement->execute()) {
                throw new Exception($statement->errorInfo());
            }

            if ($statement->rowCount() !== 1) {
                throw new OptimisticLockFailedException();
            }

            $this->updateversion++;
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
     * Returns the time the request was first submitted
     *
     * @return DateTimeImmutable
     */
    public function getDate()
    {
        return new DateTimeImmutable($this->date);
    }

    /**
     * @return bool
     */
    public function getEmailSent()
    {
        return $this->emailsent == "1";
    }

    /**
     * @param bool $emailSent
     */
    public function setEmailSent($emailSent)
    {
        $this->emailsent = $emailSent ? 1 : 0;
    }

    /**
     * @return int|null
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

    /**
     * @return bool
     */
    public function hasComments()
    {
        if ($this->hasCommentsResolved) {
            return $this->hasComments;
        }

        $commentsQuery = $this->dbObject->prepare("SELECT COUNT(*) AS num FROM comment WHERE request = :id;");
        $commentsQuery->bindValue(":id", $this->id);

        $commentsQuery->execute();

        $this->hasComments = ($commentsQuery->fetchColumn() != 0);
        $this->hasCommentsResolved = true;

        return $this->hasComments;
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
SELECT emailtemplate.defaultaction, log.action
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
        $defaultAction = $statement->fetchColumn(0);
        $logAction = $statement->fetchColumn(1);
        $statement->closeCursor();

        if ($defaultAction === null) {
            return $logAction === "Closed custom-y";
        }

        return $defaultAction === EmailTemplate::CREATED;
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

    /**
     * Returns a hash based on data within this request which can be generated easily from the data to be used to reveal
     * data to unauthorised* users.
     *
     * *:Not tool admins, check users, or the reserving user.
     *
     * @return string
     *
     * @todo future work to make invalidation better. Possibly move to the database and invalidate on relevant events?
     *       Maybe depend on the last logged action timestamp?
     */
    public function getRevealHash()
    {
        $data = $this->id         // unique per request
            . '|' . $this->ip           // }
            . '|' . $this->forwardedip  // } private data not known to those without access
            . '|' . $this->useragent    // }
            . '|' . $this->email        // }
            . '|' . $this->status; // to rudimentarily invalidate the token on status change

        return hash('sha256', $data);
    }
}

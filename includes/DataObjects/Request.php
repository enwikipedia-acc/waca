<?php
if (!defined("ACC")) {
    die();
} // Invalid entry point

class Request extends DataObject
{
    private $email;
    private $ip;
    private $name;
    private $comment;
    private $status;
    private $date;
    private $checksum;
    private $emailsent;
    private $emailconfirm;
    private $reserved;
    private $useragent;
    private $proxyip;
    
    public function save()
    {
        if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare(
                "INSERT INTO `request` (" . 
                "email, ip, name, comment, status, date, checksum, emailsent, emailconfirm, reserved, useragent, proxyip" . 
                ") VALUES (" . 
                ":email, :ip, :name, :comment, :status, CURRENT_TIMESTAMP(), :checksum, :emailsent," . 
                ":emailconfirm, :reserved, :useragent, :proxyip" . 
                ");");
			$statement->bindParam(":email", $this->email);
			$statement->bindParam(":ip", $this->ip);
			$statement->bindParam(":name", $this->name);
			$statement->bindParam(":comment", $this->comment);
			$statement->bindParam(":status", $this->status);
			$statement->bindParam(":checksum", $this->checksum);
			$statement->bindParam(":emailsent", $this->emailsent);
			$statement->bindParam(":emailconfirm", $this->emailconfirm);
			$statement->bindParam(":reserved", $this->reserved);
			$statement->bindParam(":useragent", $this->useragent);
			$statement->bindParam(":proxyip", $this->proxyip);
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
		{ // update
			$statement = $this->dbObject->prepare("UPDATE `request` SET " . 
                "status = :status, checksum = :checksum, emailsent = :emailsent, emailconfirm = :emailconfirm, " .
                "reserved = :reserved " .
                "WHERE id = :id LIMIT 1;");
			$statement->bindParam(":id", $this->id);
			$statement->bindParam(":status", $this->status);
			$statement->bindParam(":checksum", $this->checksum);
			$statement->bindParam(":emailsent", $this->emailsent);
			$statement->bindParam(":emailconfirm", $this->emailconfirm);
			$statement->bindParam(":reserved", $this->reserved);  
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		} 
        
    }
    
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getName()
    {
        return $this->name;
    }

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

    public function setEmailConfirm($emailconfirm)
    {
        $this->emailconfirm = $emailconfirm;
    }

    public function getReserved()
    {
        return $this->reserved;
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

    public function getProxyIp()
    {
        return $this->proxyip;
    }

    public function setProxyIp($proxyip)
    {
        $this->proxyip = $proxyip;
    }
}

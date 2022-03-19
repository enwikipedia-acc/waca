<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use Exception;
use PDO;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

class RequestForm extends DataObject
{
    /** @var int */
    private $enabled = 0;
    /** @var int */
    private $domain;
    /** @var string */
    private $name = '';
    /** @var string */
    private $publicendpoint = '';
    /** @var string */
    private $formcontent = '';
    /** @var int|null */
    private $overridequeue;
    /** @var string */
    private $usernamehelp;
    /** @var string */
    private $emailhelp;
    /** @var string */
    private $commentshelp;

    /**
     * @param PdoDatabase $database
     * @param int         $domain
     *
     * @return RequestForm[]
     */
    public static function getAllForms(PdoDatabase $database, int $domain)
    {
        $statement = $database->prepare("SELECT * FROM requestform WHERE domain = :domain;");
        $statement->execute([':domain' => $domain]);

        $resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        if ($resultObject === false) {
            return [];
        }

        /** @var RequestQueue $t */
        foreach ($resultObject as $t) {
            $t->setDatabase($database);
        }

        return $resultObject;
    }

    public static function getByName(PdoDatabase $database, string $name, int $domain)
    {
        $statement = $database->prepare(<<<SQL
            SELECT * FROM requestform WHERE name = :name AND domain = :domain;
SQL
        );

        $statement->execute([
            ':name' => $name,
            ':domain'  => $domain,
        ]);

        /** @var RequestForm|false $result */
        $result = $statement->fetchObject(get_called_class());

        if ($result !== false) {
            $result->setDatabase($database);
        }

        return $result;
    }

    public static function getByPublicEndpoint(PdoDatabase $database, string $endpoint)
    {
        $statement = $database->prepare(<<<SQL
            SELECT * FROM requestform WHERE publicendpoint = :endpoint;
SQL
        );

        $statement->execute([
            ':endpoint' => $endpoint
        ]);

        /** @var RequestForm|false $result */
        $result = $statement->fetchObject(get_called_class());

        if ($result !== false) {
            $result->setDatabase($database);
        }

        return $result;
    }

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO requestform (
                    enabled, domain, name, publicendpoint, formcontent, overridequeue, usernamehelp, emailhelp, commentshelp
                ) VALUES (
                    :enabled, :domain, :name, :publicendpoint, :formcontent, :overridequeue, :usernamehelp, :emailhelp, :commentshelp
                );
SQL
            );

            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":domain", $this->domain);
            $statement->bindValue(":name", $this->name);
            $statement->bindValue(":publicendpoint", $this->publicendpoint);
            $statement->bindValue(":formcontent", $this->formcontent);
            $statement->bindValue(":overridequeue", $this->overridequeue);
            $statement->bindValue(":usernamehelp", $this->usernamehelp);
            $statement->bindValue(":emailhelp", $this->emailhelp);
            $statement->bindValue(":commentshelp", $this->commentshelp);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            $statement = $this->dbObject->prepare(<<<SQL
                UPDATE requestform SET
                    enabled = :enabled,
                    domain = :domain,
                    name = :name,
                    publicendpoint = :publicendpoint,
                    formcontent = :formcontent,
                    overridequeue = :overridequeue,
                    usernamehelp = :usernamehelp,
                    emailhelp = :emailhelp,
                    commentshelp = :commentshelp,
                
                    updateversion = updateversion + 1
				WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":domain", $this->domain);
            $statement->bindValue(":name", $this->name);
            $statement->bindValue(":publicendpoint", $this->publicendpoint);
            $statement->bindValue(":formcontent", $this->formcontent);
            $statement->bindValue(":overridequeue", $this->overridequeue);
            $statement->bindValue(":usernamehelp", $this->usernamehelp);
            $statement->bindValue(":emailhelp", $this->emailhelp);
            $statement->bindValue(":commentshelp", $this->commentshelp);


            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

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
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled == 1;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled ? 1 : 0;
    }

    /**
     * @return int
     */
    public function getDomain(): int
    {
        return $this->domain;
    }

    /**
     * @param int $domain
     */
    public function setDomain(int $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPublicEndpoint(): string
    {
        return $this->publicendpoint;
    }

    /**
     * @param string $publicEndpoint
     */
    public function setPublicEndpoint(string $publicEndpoint): void
    {
        $this->publicendpoint = $publicEndpoint;
    }

    /**
     * @return string
     */
    public function getFormContent(): string
    {
        return $this->formcontent;
    }

    /**
     * @param string $formContent
     */
    public function setFormContent(string $formContent): void
    {
        $this->formcontent = $formContent;
    }

    /**
     * @return int|null
     */
    public function getOverrideQueue(): ?int
    {
        return $this->overridequeue;
    }

    /**
     * @param int|null $overrideQueue
     */
    public function setOverrideQueue(?int $overrideQueue): void
    {
        $this->overridequeue = $overrideQueue;
    }

    /**
     * @return string
     */
    public function getUsernameHelp(): ?string
    {
        return $this->usernamehelp;
    }

    /**
     * @param string $usernamehelp
     */
    public function setUsernameHelp(string $usernamehelp): void
    {
        $this->usernamehelp = $usernamehelp;
    }

    /**
     * @return string
     */
    public function getEmailHelp(): ?string
    {
        return $this->emailhelp;
    }

    /**
     * @param string $emailhelp
     */
    public function setEmailHelp(string $emailhelp): void
    {
        $this->emailhelp = $emailhelp;
    }

    /**
     * @return string
     */
    public function getCommentHelp(): ?string
    {
        return $this->commentshelp;
    }

    /**
     * @param string $commenthelp
     */
    public function setCommentHelp(string $commenthelp): void
    {
        $this->commentshelp = $commenthelp;
    }
}
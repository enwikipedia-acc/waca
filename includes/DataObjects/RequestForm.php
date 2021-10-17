<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use Exception;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;

class RequestForm extends DataObject
{
    /** @var int */
    private $enabled = 0;
    /** @var int */
    private $domain;
    /** @var string */
    private $name;
    /** @var string */
    private $publicendpoint;
    /** @var string */
    private $formcontent;
    /** @var int|null */
    private $overridequeue;

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO requestform (
                    enabled, domain, name, publicendpoint, formcontent, overridequeue
                ) VALUES (
                    :enabled, :domain, :name, :publicendpoint, :formcontent, :overridequeue
                );
SQL
            );

            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":domain", $this->domain);
            $statement->bindValue(":name", $this->name);
            $statement->bindValue(":publicendpoint", $this->publicendpoint);
            $statement->bindValue(":formcontent", $this->formcontent);
            $statement->bindValue(":overridequeue", $this->overridequeue);

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


}
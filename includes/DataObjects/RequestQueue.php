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

class RequestQueue extends DataObject
{
    /** @var int */
    private $enabled = 0;
    /** @var int */
    private $isdefault = 0;
    /** @var int */
    private $domain;
    /** @var string */
    private $apiname;
    /** @var string */
    private $displayname;
    /** @var string */
    private $header;
    /** @var string|null */
    private $help;
    /**
     * @var string
     * @deprecated Removal due as part of #607
     */
    private $logname;
    /**
     * @var string
     * @deprecated Removal due as part of #602
     */
    private $legacystatus;


    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO requestqueue (
                    enabled, isdefault, domain, apiname, displayname, header, help, logname, legacystatus
                ) VALUES (
                    :enabled, :isdefault, :domain, :apiname, :displayname, :header, :help, :logname, :legacystatus
                );
SQL
            );

            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":isdefault", $this->isdefault);
            $statement->bindValue(":domain", $this->domain);
            $statement->bindValue(":apiname", $this->apiname);
            $statement->bindValue(":displayname", $this->displayname);
            $statement->bindValue(":header", $this->header);
            $statement->bindValue(":help", $this->help);
            $statement->bindValue(":logname", $this->logname);
            $statement->bindValue(":legacystatus", $this->legacystatus);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            $statement = $this->dbObject->prepare(<<<SQL
                UPDATE requestqueue SET
                    enabled = :enabled,
                    isdefault = :isdefault,
                    domain = :domain,
                    apiname = :apiname,
                    displayname = :displayname,
                    header = :header,
                    help = :help,
                    logname = :logname,
                    legacystatus = :legacystatus,
                
                    updateversion = updateversion + 1
				WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":isdefault", $this->isdefault);
            $statement->bindValue(":domain", $this->domain);
            $statement->bindValue(":apiname", $this->apiname);
            $statement->bindValue(":displayname", $this->displayname);
            $statement->bindValue(":header", $this->header);
            $statement->bindValue(":help", $this->help);
            $statement->bindValue(":logname", $this->logname);
            $statement->bindValue(":legacystatus", $this->legacystatus);

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
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isdefault == 1;
    }

    /**
     * @param bool $isDefault
     */
    public function setDefault(bool $isDefault): void
    {
        $this->isdefault = $isDefault ? 1 : 0;
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
    public function getApiName(): string
    {
        return $this->apiname;
    }

    /**
     * @param string $apiName
     */
    public function setApiName(string $apiName): void
    {
        $this->apiname = $apiName;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayname;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName): void
    {
        $this->displayname = $displayName;
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * @param string $header
     */
    public function setHeader(string $header): void
    {
        $this->header = $header;
    }

    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }

    /**
     * @param string|null $help
     */
    public function setHelp(?string $help): void
    {
        $this->help = $help;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getLogName(): string
    {
        return $this->logname;
    }

    /**
     * @param string $logName
     *
     * @deprecated
     */
    public function setLogName(string $logName): void
    {
        $this->logname = $logName;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getLegacyStatus(): string
    {
        return $this->legacystatus;
    }

    /**
     * @param string $legacyStatus
     *
     * @deprecated
     */
    public function setLegacyStatus(string $legacyStatus): void
    {
        $this->legacystatus = $legacyStatus;
    }
}
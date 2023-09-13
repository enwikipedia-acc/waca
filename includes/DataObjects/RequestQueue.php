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
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

class RequestQueue extends DataObject
{
    const DEFAULT_DEFAULT = 'default';
    const DEFAULT_ANTISPOOF = 'antispoof';
    const DEFAULT_TITLEBLACKLIST = 'titleblacklist';

    /** @var int */
    private $enabled = 0;
    /** @var int */
    private $isdefault = 0;
    /** @var int */
    private $defaultantispoof = 0;
    /** @var int */
    private $defaulttitleblacklist = 0;
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
     * @param PdoDatabase $database
     * @param int|null    $domain
     *
     * @return RequestQueue[]
     */
    public static function getAllQueues(PdoDatabase $database, ?int $domain): array
    {
        $statement = $database->prepare(<<<SQL
            SELECT * FROM requestqueue WHERE domain = coalesce(:domain, domain);
SQL
        );
        $statement->execute([':domain'  => $domain]);

        $resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        /** @var RequestQueue $t */
        foreach ($resultObject as $t) {
            $t->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * @param PdoDatabase $database
     *
     * @return RequestQueue[]
     */
    public static function getEnabledQueues(PdoDatabase $database, int $domain)
    {
        $statement = $database->prepare(<<<SQL
            SELECT * FROM requestqueue WHERE enabled = 1 and domain = :domain;
SQL
        );
        $statement->execute([':domain' => $domain]);

        $resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        /** @var RequestQueue $t */
        foreach ($resultObject as $t) {
            $t->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * @param PdoDatabase $database
     * @param string      $apiName
     * @param int         $domain
     *
     * @return false|RequestQueue
     */
    public static function getByApiName(PdoDatabase $database, string $apiName, int $domain)
    {
        $statement = $database->prepare(<<<SQL
            SELECT * FROM requestqueue WHERE apiname = :apiName AND domain = :domain;
SQL
        );

        $statement->execute([
            ':apiName' => $apiName,
            ':domain'  => $domain,
        ]);

        /** @var RequestQueue|false $result */
        $result = $statement->fetchObject(get_called_class());

        if ($result !== false) {
            $result->setDatabase($database);
        }

        return $result;
    }

    /**
     * @param PdoDatabase $database
     * @param string      $displayName
     * @param int         $domain
     *
     * @return false|RequestQueue
     */
    public static function getByDisplayName(PdoDatabase $database, string $displayName, int $domain)
    {
        $statement = $database->prepare(<<<SQL
            SELECT * FROM requestqueue WHERE displayname = :displayName AND domain = :domain;
SQL
        );

        $statement->execute([
            ':displayName' => $displayName,
            ':domain'      => $domain,
        ]);

        /** @var RequestQueue|false $result */
        $result = $statement->fetchObject(get_called_class());

        if ($result !== false) {
            $result->setDatabase($database);
        }

        return $result;
    }

    /**
     * @param PdoDatabase $database
     * @param string      $header
     * @param int         $domain
     *
     * @return false|RequestQueue
     */
    public static function getByHeader(PdoDatabase $database, string $header, int $domain)
    {
        $statement = $database->prepare(<<<SQL
            SELECT * FROM requestqueue WHERE header = :header AND domain = :domain;
SQL
        );

        $statement->execute([
            ':header' => $header,
            ':domain' => $domain,
        ]);

        /** @var RequestQueue|false $result */
        $result = $statement->fetchObject(get_called_class());

        if ($result !== false) {
            $result->setDatabase($database);
        }

        return $result;
    }

    /**
     * @param PdoDatabase $database
     * @param int         $domain
     *
     * @param string      $defaultType The type of default queue to get.
     *
     * @return false|RequestQueue
     * @throws ApplicationLogicException
     */
    public static function getDefaultQueue(PdoDatabase $database, int $domain, string $defaultType = 'default')
    {
        switch ($defaultType) {
            case self::DEFAULT_DEFAULT:
                $sql = 'SELECT * FROM requestqueue WHERE isdefault = 1 AND domain = :domain;';
                break;
            case self::DEFAULT_ANTISPOOF:
                $sql = 'SELECT * FROM requestqueue WHERE defaultantispoof = 1 AND domain = :domain;';
                break;
            case self::DEFAULT_TITLEBLACKLIST:
                $sql = 'SELECT * FROM requestqueue WHERE defaulttitleblacklist = 1 AND domain = :domain;';
                break;
            default:
                throw new ApplicationLogicException('Unknown request for default queue');
        }

        $statement = $database->prepare($sql);

        $statement->execute([':domain' => $domain]);

        /** @var RequestQueue|false $result */
        $result = $statement->fetchObject(get_called_class());

        if ($result !== false) {
            $result->setDatabase($database);
        }

        return $result;
    }

    public function save()
    {
        // find and squish existing defaults
        if ($this->isDefault()) {
            $squishStatement = $this->dbObject->prepare('UPDATE requestqueue SET isdefault = 0 WHERE isdefault = 1 AND domain = :domain;');
            $squishStatement->execute([':domain' => $this->domain]);
        }

        if ($this->isDefaultAntispoof()) {
            $squishStatement = $this->dbObject->prepare('UPDATE requestqueue SET defaultantispoof = 0 WHERE defaultantispoof = 1 AND domain = :domain;');
            $squishStatement->execute([':domain' => $this->domain]);
        }

        if ($this->isDefaultTitleBlacklist()) {
            $squishStatement = $this->dbObject->prepare('UPDATE requestqueue SET defaulttitleblacklist = 0 WHERE defaulttitleblacklist = 1 AND domain = :domain;');
            $squishStatement->execute([':domain' => $this->domain]);
        }

        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO requestqueue (
                    enabled, isdefault, defaultantispoof, defaulttitleblacklist, domain, apiname, displayname, header, help, logname
                ) VALUES (
                    :enabled, :isdefault, :defaultantispoof, :defaulttitleblacklist, :domain, :apiname, :displayname, :header, :help, :logname
                );
SQL
            );

            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":isdefault", $this->isdefault);
            $statement->bindValue(":defaultantispoof", $this->defaultantispoof);
            $statement->bindValue(":defaulttitleblacklist", $this->defaulttitleblacklist);
            $statement->bindValue(":domain", $this->domain);
            $statement->bindValue(":apiname", $this->apiname);
            $statement->bindValue(":displayname", $this->displayname);
            $statement->bindValue(":header", $this->header);
            $statement->bindValue(":help", $this->help);
            $statement->bindValue(":logname", $this->logname);

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
                    defaultantispoof = :defaultantispoof,
                    defaulttitleblacklist = :defaulttitleblacklist,
                    domain = :domain,
                    apiname = :apiname,
                    displayname = :displayname,
                    header = :header,
                    help = :help,
                    logname = :logname,
                
                    updateversion = updateversion + 1
				WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":isdefault", $this->isdefault);
            $statement->bindValue(":defaultantispoof", $this->defaultantispoof);
            $statement->bindValue(":defaulttitleblacklist", $this->defaulttitleblacklist);
            $statement->bindValue(":domain", $this->domain);
            $statement->bindValue(":apiname", $this->apiname);
            $statement->bindValue(":displayname", $this->displayname);
            $statement->bindValue(":header", $this->header);
            $statement->bindValue(":help", $this->help);
            $statement->bindValue(":logname", $this->logname);

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
     * @return bool
     */
    public function isDefaultAntispoof(): bool
    {
        return $this->defaultantispoof == 1;
    }

    /**
     * @param bool $isDefault
     */
    public function setDefaultAntispoof(bool $isDefault): void
    {
        $this->defaultantispoof = $isDefault ? 1 : 0;
    }

    /**
     * @return bool
     */
    public function isDefaultTitleBlacklist(): bool
    {
        return $this->defaulttitleblacklist == 1;
    }

    /**
     * @param bool $isDefault
     */
    public function setDefaultTitleBlacklist(bool $isDefault): void
    {
        $this->defaulttitleblacklist = $isDefault ? 1 : 0;
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
}
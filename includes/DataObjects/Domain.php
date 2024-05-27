<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\DataObjects;

use Exception;
use PDO;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;
use Waca\WebRequest;

class Domain extends DataObject
{
    /** @var string */
    private $shortname;
    /** @var string */
    private $longname;
    /** @var string */
    private $wikiarticlepath;
    /** @var string */
    private $wikiapipath;
    /** @var int */
    private $enabled = 0;
    /** @var int|null */
    private $defaultclose;
    /** @var string */
    private $defaultlanguage = 'en';
    /** @var string */
    private $emailreplyaddress;
    /** @var string|null */
    private $notificationtarget;
    /** @var string */
    private $localdocumentation;

    /** @var Domain Cache variable of the current domain */
    private static $currentDomain;

    public static function getCurrent(PdoDatabase $database)
    {
        if (self::$currentDomain === null) {
            $sessionDomain = WebRequest::getSessionDomain();

            if ($sessionDomain !== null) {
                /** @var Domain $domain */
                $domain = self::getById($sessionDomain, $database);

                if ($domain === false) {
                    self::$currentDomain = self::getById(1, $database); // FIXME: #594 User::getCurrent($database)->getDefaultDomain();
                }
                else {
                    self::$currentDomain = $domain;
                }
            }
            else {
                self::$currentDomain = self::getById(1, $database); // FIXME: #594 User::getCurrent($database)->getDefaultDomain();
            }
        }

        return self::$currentDomain;
    }

    public static function getByShortName(string $shortName, PdoDatabase $database)
    {
        $statement = $database->prepare(<<<SQL
            SELECT * FROM domain WHERE shortname = :name;
SQL
        );

        $statement->execute([
            ':name' => $shortName,
        ]);

        /** @var RequestForm|false $result */
        $result = $statement->fetchObject(get_called_class());

        if ($result !== false) {
            $result->setDatabase($database);
        }

        return $result;
    }

    public static function getAll(PdoDatabase $database) {
        $statement = $database->prepare("SELECT * FROM domain;");
        $statement->execute();

        $resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        /** @var Domain $t */
        foreach ($resultObject as $t) {
            $t->setDatabase($database);
        }

        return $resultObject;
    }

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO domain (
                    shortname, longname, wikiarticlepath, wikiapipath, enabled, defaultclose, defaultlanguage, 
                    emailreplyaddress, notificationtarget, localdocumentation
                ) VALUES (
                    :shortname, :longname, :wikiarticlepath, :wikiapipath, :enabled, :defaultclose, :defaultlanguage,
                    :emailreplyaddress, :notificationtarget, :localdocumentation
                );
SQL
            );

            $statement->bindValue(":shortname", $this->shortname);
            $statement->bindValue(":longname", $this->longname);
            $statement->bindValue(":wikiarticlepath", $this->wikiarticlepath);
            $statement->bindValue(":wikiapipath", $this->wikiapipath);
            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":defaultclose", $this->defaultclose);
            $statement->bindValue(":defaultlanguage", $this->defaultlanguage);
            $statement->bindValue(":emailreplyaddress", $this->emailreplyaddress);
            $statement->bindValue(":notificationtarget", $this->notificationtarget);
            $statement->bindValue(":localdocumentation", $this->localdocumentation);


            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            $statement = $this->dbObject->prepare(<<<SQL
                UPDATE domain SET
                    longname = :longname,
                    wikiarticlepath = :wikiarticlepath,
                    wikiapipath = :wikiapipath,
                    enabled = :enabled,
                    defaultclose = :defaultclose,
                    defaultlanguage = :defaultlanguage,
                    emailreplyaddress = :emailreplyaddress,
                    notificationtarget = :notificationtarget,
                    localdocumentation = :localdocumentation,
                
                    updateversion = updateversion + 1
				WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(":longname", $this->longname);
            $statement->bindValue(":wikiarticlepath", $this->wikiarticlepath);
            $statement->bindValue(":wikiapipath", $this->wikiapipath);
            $statement->bindValue(":enabled", $this->enabled);
            $statement->bindValue(":defaultclose", $this->defaultclose);
            $statement->bindValue(":defaultlanguage", $this->defaultlanguage);
            $statement->bindValue(":emailreplyaddress", $this->emailreplyaddress);
            $statement->bindValue(":notificationtarget", $this->notificationtarget);
            $statement->bindValue(":localdocumentation", $this->localdocumentation);

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
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortname;
    }

    /**
     * @param string $shortName
     */
    public function setShortName(string $shortName): void
    {
        $this->shortname = $shortName;
    }

    /**
     * @return string
     */
    public function getLongName(): string
    {
        return $this->longname;
    }

    /**
     * @param string $longName
     */
    public function setLongName(string $longName): void
    {
        $this->longname = $longName;
    }

    /**
     * @return string
     */
    public function getWikiArticlePath(): string
    {
        return $this->wikiarticlepath;
    }

    /**
     * @param string $wikiArticlePath
     */
    public function setWikiArticlePath(string $wikiArticlePath): void
    {
        $this->wikiarticlepath = $wikiArticlePath;
    }

    /**
     * @return string
     */
    public function getWikiApiPath(): string
    {
        return $this->wikiapipath;
    }

    /**
     * @param string $wikiApiPath
     */
    public function setWikiApiPath(string $wikiApiPath): void
    {
        $this->wikiapipath = $wikiApiPath;
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
    public function getDefaultClose(): ?int
    {
        return $this->defaultclose;
    }

    /**
     * @param int $defaultClose
     */
    public function setDefaultClose(?int $defaultClose): void
    {
        $this->defaultclose = $defaultClose;
    }

    /**
     * @return string
     */
    public function getDefaultLanguage(): string
    {
        return $this->defaultlanguage;
    }

    /**
     * @param string $defaultLanguage
     */
    public function setDefaultLanguage(string $defaultLanguage): void
    {
        $this->defaultlanguage = $defaultLanguage;
    }

    /**
     * @return string
     */
    public function getEmailReplyAddress(): string
    {
        return $this->emailreplyaddress;
    }

    /**
     * @param string $emailReplyAddress
     */
    public function setEmailReplyAddress(string $emailReplyAddress): void
    {
        $this->emailreplyaddress = $emailReplyAddress;
    }

    /**
     * @return string|null
     */
    public function getNotificationTarget(): ?string
    {
        return $this->notificationtarget;
    }

    /**
     * @param string|null $notificationTarget
     */
    public function setNotificationTarget(?string $notificationTarget): void
    {
        $this->notificationtarget = $notificationTarget;
    }

    /**
     * @return string
     */
    public function getLocalDocumentation(): string
    {
        return $this->localdocumentation;
    }

    /**
     * @param string $localDocumentation
     */
    public function setLocalDocumentation(string $localDocumentation): void
    {
        $this->localdocumentation = $localDocumentation;
    }
}
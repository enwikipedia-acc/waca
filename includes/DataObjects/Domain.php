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
    private $emailsender;
    /** @var string|null */
    private $notificationtarget;

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO domain (
                    shortname, longname, wikiarticlepath, wikiapipath, enabled, defaultclose, defaultlanguage, 
                    emailsender, notificationtarget
                ) VALUES (
                    :shortname, :longname, :wikiarticlepath, :wikiapipath, :enabled, :defaultclose, :defaultlanguage,
                    :emailsender, :notificationtarget
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
            $statement->bindValue(":emailsender", $this->emailsender);
            $statement->bindValue(":notificationtarget", $this->notificationtarget);

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
                    emailsender = :emailsender,
                    notificationtarget = :notificationtarget,
                
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
            $statement->bindValue(":emailsender", $this->emailsender);
            $statement->bindValue(":notificationtarget", $this->notificationtarget);

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
    public function getEmailSender(): string
    {
        return $this->emailsender;
    }

    /**
     * @param string $emailSender
     */
    public function setEmailSender(string $emailSender): void
    {
        $this->emailsender = $emailSender;
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


}
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

/**
 * Email template data object
 *
 * This is the close reasons thing.
 */
class EmailTemplate extends DataObject
{
    const ACTION_CREATED = 'created';
    const ACTION_NOT_CREATED = 'not created';
    const ACTION_NONE = 'none';
    const ACTION_DEFER = 'defer';

    /** @var string the name of the template */
    private $name;
    private $text;
    /** @var string|null */
    private $jsquestion;
    private $active = 1;
    private $preloadonly = 0;
    private $defaultaction = self::ACTION_NOT_CREATED;
    private $queue;
    /** @var int */
    private $domain;

    /**
     * Gets active non-preload templates
     *
     * @param string      $defaultAction Default action to take (EmailTemplate::ACTION_CREATED or EmailTemplate::ACTION_NOT_CREATED)
     * @param PdoDatabase $database
     * @param int         $domain
     * @param int|null    $filter        Template IDs to filter out
     *
     * @return array|false
     */
    public static function getActiveNonpreloadTemplates($defaultAction, PdoDatabase $database, int $domain, ?int $filter = null)
    {
        $statement = $database->prepare(<<<SQL
SELECT * FROM `emailtemplate`
WHERE defaultaction = :forcreated AND active = 1 AND preloadonly = 0 AND (:skipFilter = 1 OR id <> :filter) AND domain = :domain;
SQL
        );
        $statement->bindValue(":forcreated", $defaultAction);
        $statement->bindValue(":filter", $filter);
        $statement->bindValue(":skipFilter", $filter === null ? 1 : 0);
        $statement->bindValue(":domain", $domain);

        $statement->execute();

        $resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        /** @var EmailTemplate $t */
        foreach ($resultObject as $t) {
            $t->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * Gets active non-preload and preload templates, optionally filtered by the default action.
     *
     * @param null|bool|string $defaultAction Default action to take (EmailTemplate::ACTION_CREATED,
     *                                        EmailTemplate::ACTION_NOT_CREATED, or EmailTemplate::ACTION_NONE), or optionally null to
     *                                        just get everything.
     * @param PdoDatabase      $database
     * @param int              $domain
     *
     * @return array|false
     */
    public static function getAllActiveTemplates($defaultAction, PdoDatabase $database, int $domain)
    {
        if ($defaultAction === false) {
            $statement = $database->prepare(
                "SELECT * FROM `emailtemplate` WHERE defaultaction NOT IN ('created', 'not created') AND active = 1 AND domain = :domain;");
        }
        elseif ($defaultAction === null) {
            $statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE active = 1 AND domain = :domain;");
        }
        else {
            $statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE defaultaction = :forcreated AND active = 1 AND domain = :domain;");
            $statement->bindValue(":forcreated", $defaultAction);
        }

        $statement->bindValue(":domain", $domain);

        $statement->execute();

        $resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        /** @var EmailTemplate $t */
        foreach ($resultObject as $t) {
            $t->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * Gets all the inactive templates
     *
     * @param PdoDatabase $database
     * @param int         $domain
     *
     * @return array
     */
    public static function getAllInactiveTemplates(PdoDatabase $database, int $domain)
    {
        $statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE active = 0 AND domain = :domain;");
        $statement->execute([':domain' => $domain]);

        $resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        /** @var EmailTemplate $t */
        foreach ($resultObject as $t) {
            $t->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * @param string      $name
     * @param PdoDatabase $database
     * @param int         $domain
     *
     * @return EmailTemplate|false
     */
    public static function getByName($name, PdoDatabase $database, int $domain)
    {
        $statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE name = :name AND domain = :domain LIMIT 1;");
        $statement->bindValue(":name", $name);
        $statement->bindValue(":domain", $domain);

        $statement->execute();

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject != false) {
            $resultObject->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * @return EmailTemplate
     */
    public static function getDroppedTemplate()
    {
        $t = new EmailTemplate();
        $t->id = 0;
        $t->active = 1;
        $t->defaultaction = self::ACTION_NONE;
        $t->name = 'Dropped';

        return $t;
    }

    /**
     * @throws Exception
     */
    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
INSERT INTO `emailtemplate` (name, text, jsquestion, defaultaction, active, preloadonly, queue, domain)
VALUES (:name, :text, :jsquestion, :defaultaction, :active, :preloadonly, :queue, :domain);
SQL
            );
            $statement->bindValue(":name", $this->name);
            $statement->bindValue(":text", $this->text);
            $statement->bindValue(":jsquestion", $this->jsquestion);
            $statement->bindValue(":defaultaction", $this->defaultaction);
            $statement->bindValue(":active", $this->active);
            $statement->bindValue(":preloadonly", $this->preloadonly);
            $statement->bindValue(":queue", $this->queue);
            $statement->bindValue(":domain", $this->domain);

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
UPDATE `emailtemplate`
SET name = :name,
	text = :text,
	jsquestion = :jsquestion,
	defaultaction = :defaultaction,
	active = :active,
	preloadonly = :preloadonly,
    queue = :queue,
	updateversion = updateversion + 1
WHERE id = :id AND updateversion = :updateversion;
SQL
            );
            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(':name', $this->name);
            $statement->bindValue(":text", $this->text);
            $statement->bindValue(":jsquestion", $this->jsquestion);
            $statement->bindValue(":defaultaction", $this->defaultaction);
            $statement->bindValue(":active", $this->active);
            $statement->bindValue(":preloadonly", $this->preloadonly);
            $statement->bindValue(":queue", $this->queue);

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
     * Override delete() from DataObject
     */
    public function delete()
    {
        throw new Exception("You shouldn't be doing that, you'll break logs.");
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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string|null
     */
    public function getJsquestion()
    {
        return $this->jsquestion;
    }

    /**
     * @param string $jsquestion
     */
    public function setJsquestion($jsquestion)
    {
        $this->jsquestion = $jsquestion;
    }

    /**
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->defaultaction;
    }

    /**
     * @param string $defaultAction
     */
    public function setDefaultAction($defaultAction)
    {
        $this->defaultaction = $defaultAction;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active == 1;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active ? 1 : 0;
    }

    /**
     * @return bool
     */
    public function getPreloadOnly()
    {
        return $this->preloadonly == 1;
    }

    /**
     * @param bool $preloadonly
     */
    public function setPreloadOnly($preloadonly)
    {
        $this->preloadonly = $preloadonly ? 1 : 0;
    }

    /**
     * @return int|null
     */
    public function getQueue(): ?int
    {
        return $this->queue;
    }

    /**
     * @return RequestQueue|null
     */
    public function getQueueObject(): ?RequestQueue
    {
        if ($this->queue === null) {
            return null;
        }

        /** @var $dataObject RequestQueue|false */
        $dataObject = RequestQueue::getById($this->queue, $this->getDatabase());

        if ($dataObject === false) {
            return null;
        }

        return $dataObject;
    }

    /**
     * @param int|null $queue
     */
    public function setQueue(?int $queue): void
    {
        $this->queue = $queue;
    }

    public function getDomain(): int
    {
        return $this->domain;
    }

    public function setDomain(int $domain): void
    {
        $this->domain = $domain;
    }
}

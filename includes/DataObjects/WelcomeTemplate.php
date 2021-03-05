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
 * Welcome template data object
 */
class WelcomeTemplate extends DataObject
{
    /** @var string */
    private $usercode;
    /** @var string */
    private $botcode;
    private $usageCache;
    private $deleted = 0;

    /**
     * Summary of getAll
     *
     * @param PdoDatabase $database
     *
     * @return WelcomeTemplate[]
     */
    public static function getAll(PdoDatabase $database)
    {
        $statement = $database->prepare("SELECT * FROM welcometemplate WHERE deleted = 0;");

        $statement->execute();

        $result = array();
        /** @var WelcomeTemplate $v */
        foreach ($statement->fetchAll(PDO::FETCH_CLASS, self::class) as $v) {
            $v->setDatabase($database);
            $result[] = $v;
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
INSERT INTO welcometemplate (usercode, botcode) VALUES (:usercode, :botcode);
SQL
            );
            $statement->bindValue(":usercode", $this->usercode);
            $statement->bindValue(":botcode", $this->botcode);

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
UPDATE `welcometemplate`
SET usercode = :usercode, botcode = :botcode, updateversion = updateversion + 1
WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(':usercode', $this->usercode);
            $statement->bindValue(':botcode', $this->botcode);

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
    public function getUserCode()
    {
        return $this->usercode;
    }

    /**
     * @param string $usercode
     */
    public function setUserCode($usercode)
    {
        $this->usercode = $usercode;
    }

    /**
     * @return string
     */
    public function getBotCode()
    {
        return $this->botcode;
    }

    /**
     * @param string $botcode
     */
    public function setBotCode($botcode)
    {
        $this->botcode = $botcode;
    }

    /**
     * @return User[]
     */
    public function getUsersUsingTemplate()
    {
        if ($this->usageCache === null) {
            $statement = $this->dbObject->prepare("SELECT * FROM user WHERE welcome_template = :id;");

            $statement->execute(array(":id" => $this->id));

            $result = array();
            /** @var WelcomeTemplate $v */
            foreach ($statement->fetchAll(PDO::FETCH_CLASS, User::class) as $v) {
                $v->setDatabase($this->dbObject);
                $result[] = $v;
            }

            $this->usageCache = $result;
        }

        return $this->usageCache;
    }

    /**
     * Deletes the object from the database
     */
    public function delete()
    {
        if ($this->id === null) {
            // wtf?
            return;
        }

        $deleteQuery = "UPDATE welcometemplate SET deleted = 1 WHERE id = :id AND updateversion = :updateversion;";
        $statement = $this->dbObject->prepare($deleteQuery);

        $statement->bindValue(":id", $this->id);
        $statement->bindValue(":updateversion", $this->updateversion);
        $statement->execute();

        if ($statement->rowCount() !== 1) {
            throw new OptimisticLockFailedException();
        }
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return ((int)$this->deleted) === 1;
    }

    public function getSectionHeader()
    {
        // Hard-coded for future update ability to change this per-template. This has beem moved from being hard-coded
        // directly in the welcome task, and safely permits us to show the header in the welcome template preview
        return "Welcome!";
    }

    public function getBotCodeForWikiSave(string $request, $creator)
    {
        $templateText = $this->getBotCode();

        $templateText = str_replace('$request', $request, $templateText);
        $templateText = str_replace('$creator', $creator, $templateText);

        // legacy; these have been removed in Prod for now, but I'm keeping them in case someone follows the
        // instructions in prod prior to deployment.
        $templateText = str_replace('$signature', '~~~~', $templateText);
        $templateText = str_replace('$username', $creator, $templateText);

        return $templateText;
    }
}

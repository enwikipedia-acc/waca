<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use DateTimeImmutable;
use Exception;
use Waca\DataObject;

/**
 * Notification short summary.
 *
 * Notification description.
 *
 * @version 1.0
 * @author  stwalkerster
 */
class Notification extends DataObject
{
    private $date;
    private $type;
    private $text;

    public function delete()
    {
        throw new Exception("You shouldn't be doing this...");
    }

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare("INSERT INTO notification ( type, text ) VALUES ( :type, :text );");
            $statement->bindValue(":type", $this->type);
            $statement->bindValue(":text", $this->text);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            throw new Exception("You shouldn't be doing this...");
        }
    }

    public function getDate()
    {
        return new DateTimeImmutable($this->date);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Summary of setType
     *
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Summary of setText
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}

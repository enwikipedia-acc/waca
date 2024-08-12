<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\DataObjects;

use DateTimeImmutable;
use Exception;
use PDO;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

/**
 * Comment data object
 */
class Comment extends DataObject
{
    private $time;
    private $user;
    private $comment;
    private $visibility = "user";
    private $request;
    private $flagged = 0;
    private $edited;

    /**
     * Retrieves all comments for a request, optionally filtered
     *
     * @param integer     $id             Request ID to search by
     * @param PdoDatabase $database
     * @param bool        $showRestricted True to show all comments, False to show only unprotected comments, and protected
     *                                    comments visible to $userId
     * @param bool        $showCheckuser
     * @param null|int    $userId         User to filter by
     *
     * @return Comment[]
     */
    public static function getForRequest($id, PdoDatabase $database, $showRestricted = false, $showCheckuser = false, $userId = null)
    {
        $parameters = ['requester', 'user'];
        if ($showCheckuser) {
            $parameters[] = 'checkuser';
        }
        if ($showRestricted) {
            $parameters[] = 'admin';
        }

        $visibilityPlaceholders = str_repeat('?,', count($parameters) - 1) . '?';

        $statement = $database->prepare(<<<SQL
SELECT * FROM comment
WHERE (visibility in (${visibilityPlaceholders}) OR user = ?) AND request = ?;
SQL
        );

        $parameters[] = $userId;
        $parameters[] = $id;

        $statement->execute($parameters);

        $result = array();
        /** @var Comment $v */
        foreach ($statement->fetchAll(PDO::FETCH_CLASS, get_called_class()) as $v) {
            $v->setDatabase($database);
            $result[] = $v;
        }

        return $result;
    }

    public static function getFlaggedComments(PdoDatabase $database, int $domain)
    {
        $statement = $database->prepare('SELECT c.* FROM comment c INNER JOIN request r ON c.request = r.id WHERE c.flagged = 1 AND r.domain = :domain;');
        $statement->execute([':domain' => $domain]);

        $result = array();
        /** @var Comment $v */
        foreach ($statement->fetchAll(PDO::FETCH_CLASS, get_called_class()) as $v) {
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
INSERT INTO comment ( time, user, comment, visibility, request, flagged )
VALUES ( CURRENT_TIMESTAMP(), :user, :comment, :visibility, :request, :flagged );
SQL
            );
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":comment", $this->comment);
            $statement->bindValue(":visibility", $this->visibility);
            $statement->bindValue(":request", $this->request);
            $statement->bindValue(":flagged", $this->flagged);

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
UPDATE comment
SET comment = :comment, visibility = :visibility, flagged = :flagged, edited = :edited, updateversion = updateversion + 1
WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(':comment', $this->comment);
            $statement->bindValue(':visibility', $this->visibility);
            $statement->bindValue(":flagged", $this->flagged);
            $statement->bindValue(":edited", $this->edited);

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
     * @return DateTimeImmutable
     */
    public function getTime()
    {
        return new DateTimeImmutable($this->time);
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * @return int
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param int $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function getFlagged() : bool
    {
        return $this->flagged == 1;
    }

    /**
     * @param bool $flagged
     */
    public function setFlagged(bool $flagged): void
    {
        $this->flagged = $flagged ? 1 : 0;
    }

    public function touchEdited() : void
    {
        $dateTimeImmutable = new DateTimeImmutable("now");
        $this->edited = $dateTimeImmutable->format('Y-m-d H:i:s');
    }

    public function getEdited() : ?DateTimeImmutable
    {
        if ($this->edited === null) {
            return null;
        }

        return new DateTimeImmutable($this->edited);
    }
}

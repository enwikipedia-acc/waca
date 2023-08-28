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
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;

class OAuthToken extends DataObject
{
    /** @var int */
    private $user;
    /** @var string */
    private $token;
    /** @var string */
    private $secret;
    /** @var string */
    private $type;
    /** @var string */
    private $expiry;

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO oauthtoken ( user, token, secret, type, expiry )
                VALUES ( :user, :token, :secret, :type, :expiry );
SQL
            );
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":token", $this->token);
            $statement->bindValue(":secret", $this->secret);
            $statement->bindValue(":type", $this->type);
            $statement->bindValue(":expiry", $this->expiry);

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
                UPDATE oauthtoken
                SET   token = :token
                    , secret = :secret
                    , type = :type
                    , expiry = :expiry
                    , updateversion = updateversion + 1
                WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(":token", $this->token);
            $statement->bindValue(":secret", $this->secret);
            $statement->bindValue(":type", $this->type);
            $statement->bindValue(":expiry", $this->expiry);

            if (!$statement->execute()) {
                throw new Exception($statement->errorInfo());
            }

            if ($statement->rowCount() !== 1) {
                throw new OptimisticLockFailedException();
            }

            $this->updateversion++;
        }
    }

    #region properties

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUserId($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param mixed $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param string $expiry
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
    }
    #endregion

}
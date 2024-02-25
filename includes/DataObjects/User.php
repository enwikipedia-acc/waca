<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use DateTime;
use Exception;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\IdentificationVerifier;
use Waca\PdoDatabase;
use Waca\WebRequest;

/**
 * User data object
 */
class User extends DataObject
{
    const STATUS_ACTIVE = 'Active';
    const STATUS_SUSPENDED = 'Suspended';
    const STATUS_DECLINED = 'Declined';
    const STATUS_NEW = 'New';

    private static CommunityUser $community;

    private $username;
    private $email;
    private $status = self::STATUS_NEW;
    private $onwikiname;
    private $lastactive = "0000-00-00 00:00:00";
    private $forcelogout = 0;
    private $forceidentified = null;
    private $confirmationdiff = 0;
    /** @var User Cache variable of the current user - it's never going to change in the middle of a request. */
    private static $currentUser;
    #region Object load methods

    /**
     * Gets the currently logged in user
     *
     * @param PdoDatabase $database
     *
     * @return User|CommunityUser
     */
    public static function getCurrent(PdoDatabase $database)
    {
        if (self::$currentUser === null) {
            $sessionId = WebRequest::getSessionUserId();

            if ($sessionId !== null) {
                /** @var User $user */
                $user = self::getById($sessionId, $database);

                if ($user === false) {
                    self::$currentUser = new CommunityUser();
                }
                else {
                    self::$currentUser = $user;
                }
            }
            else {
                $anonymousCoward = new CommunityUser();

                self::$currentUser = $anonymousCoward;
            }
        }

        return self::$currentUser;
    }

    /**
     * Gets a user by their user ID
     *
     * Pass -1 to get the community user.
     *
     * @param int|null    $id
     * @param PdoDatabase $database
     *
     * @return User|false
     */
    public static function getById($id, PdoDatabase $database)
    {
        if ($id === null || $id == -1) {
            return new CommunityUser();
        }

        /** @var User|false $user */
        $user = parent::getById($id, $database);

        return $user;
    }

    public static function getCommunity(): CommunityUser
    {
        if (!isset(self::$community)) {
            self::$community = new CommunityUser();
        }

        return self::$community;
    }

    /**
     * Gets a user by their username
     *
     * @param  string      $username
     * @param  PdoDatabase $database
     *
     * @return CommunityUser|User|false
     */
    public static function getByUsername($username, PdoDatabase $database)
    {
        if ($username === self::getCommunity()->getUsername()) {
            return new CommunityUser();
        }

        $statement = $database->prepare("SELECT * FROM user WHERE username = :id LIMIT 1;");
        $statement->bindValue(":id", $username);

        $statement->execute();

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject != false) {
            $resultObject->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * Gets a user by their on-wiki username.
     *
     * @param string      $username
     * @param PdoDatabase $database
     *
     * @return User|false
     */
    public static function getByOnWikiUsername($username, PdoDatabase $database)
    {
        $statement = $database->prepare("SELECT * FROM user WHERE onwikiname = :id LIMIT 1;");
        $statement->bindValue(":id", $username);
        $statement->execute();

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject != false) {
            $resultObject->setDatabase($database);

            return $resultObject;
        }

        return false;
    }

    #endregion

    /**
     * Saves the current object
     *
     * @throws Exception
     */
    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
				INSERT INTO `user` ( 
					username, email, status, onwikiname, 
					lastactive, forcelogout, forceidentified,
					confirmationdiff
				) VALUES (
					:username, :email, :status, :onwikiname,
					:lastactive, :forcelogout, :forceidentified,
					:confirmationdiff
				);
SQL
            );
            $statement->bindValue(":username", $this->username);
            $statement->bindValue(":email", $this->email);
            $statement->bindValue(":status", $this->status);
            $statement->bindValue(":onwikiname", $this->onwikiname);
            $statement->bindValue(":lastactive", $this->lastactive);
            $statement->bindValue(":forcelogout", $this->forcelogout);
            $statement->bindValue(":forceidentified", $this->forceidentified);
            $statement->bindValue(":confirmationdiff", $this->confirmationdiff);

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
				UPDATE `user` SET 
					username = :username, email = :email, 
					status = :status,
					onwikiname = :onwikiname, 
					lastactive = :lastactive,
					forcelogout = :forcelogout, 
					forceidentified = :forceidentified,
					confirmationdiff = :confirmationdiff,
                    updateversion = updateversion + 1
				WHERE id = :id AND updateversion = :updateversion;
SQL
            );
            $statement->bindValue(":forceidentified", $this->forceidentified);

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(':username', $this->username);
            $statement->bindValue(':email', $this->email);
            $statement->bindValue(':status', $this->status);
            $statement->bindValue(':onwikiname', $this->onwikiname);
            $statement->bindValue(':lastactive', $this->lastactive);
            $statement->bindValue(':forcelogout', $this->forcelogout);
            $statement->bindValue(':forceidentified', $this->forceidentified);
            $statement->bindValue(':confirmationdiff', $this->confirmationdiff);

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
     * Gets the tool username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the tool username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;

        // If this isn't a brand new user, then it's a rename, force the logout
        if (!$this->isNew()) {
            $this->forcelogout = 1;
        }
    }

    /**
     * Gets the user's email address
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the user's email address
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Gets the status (User, Admin, Suspended, etc - excludes checkuser) of the user.
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Gets the user's on-wiki name
     * @return string
     */
    public function getOnWikiName()
    {
        return $this->onwikiname;
    }

    /**
     * Sets the user's on-wiki name
     *
     * This can have interesting side-effects with OAuth.
     *
     * @param string $onWikiName
     */
    public function setOnWikiName($onWikiName)
    {
        $this->onwikiname = $onWikiName;
    }

    /**
     * Gets the last activity date for the user
     *
     * @return string
     * @todo This should probably return an instance of DateTime
     */
    public function getLastActive()
    {
        return $this->lastactive;
    }

    /**
     * Gets the user's forced logout status
     *
     * @return bool
     */
    public function getForceLogout()
    {
        return $this->forcelogout == 1;
    }

    /**
     * Sets the user's forced logout status
     *
     * @param bool $forceLogout
     */
    public function setForceLogout($forceLogout)
    {
        $this->forcelogout = $forceLogout ? 1 : 0;
    }

    /**
     * Gets the user's confirmation diff. Unused if OAuth is in use.
     * @return int the diff ID
     */
    public function getConfirmationDiff()
    {
        return $this->confirmationdiff;
    }

    /**
     * Sets the user's confirmation diff.
     *
     * @param int $confirmationDiff
     */
    public function setConfirmationDiff($confirmationDiff)
    {
        $this->confirmationdiff = $confirmationDiff;
    }

    #endregion

    #region user access checks

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Tests if the user is identified
     *
     * @param IdentificationVerifier $iv
     *
     * @return bool
     * @todo     Figure out what on earth is going on with PDO's typecasting here.  Apparently, it returns string("0") for
     *       the force-unidentified case, and int(1) for the identified case?!  This is quite ugly, but probably needed
     *       to play it safe for now.
     * @category Security-Critical
     */
    public function isIdentified(IdentificationVerifier $iv)
    {
        if ($this->forceidentified === 0 || $this->forceidentified === "0") {
            // User forced to unidentified in the database.
            return false;
        }
        elseif ($this->forceidentified === 1 || $this->forceidentified === "1") {
            // User forced to identified in the database.
            return true;
        }
        else {
            // User not forced to any particular identified status; consult IdentificationVerifier
            return $iv->isUserIdentified($this->getOnWikiName());
        }
    }

    /**
     * DO NOT USE FOR TESTING IDENTIFICATION STATUS.
     *
     * @return bool|null
     */
    public function getForceIdentified()
    {
        return $this->forceidentified;
    }

    /**
     * Tests if the user is suspended
     * @return bool
     * @category Security-Critical
     */
    public function isSuspended()
    {
        return $this->status == self::STATUS_SUSPENDED;
    }

    /**
     * Tests if the user is new
     * @return bool
     * @category Security-Critical
     */
    public function isNewUser()
    {
        return $this->status == self::STATUS_NEW;
    }

    /**
     * Tests if the user has been declined access to the tool
     * @return bool
     * @category Security-Critical
     */
    public function isDeclined()
    {
        return $this->status == self::STATUS_DECLINED;
    }

    /**
     * Tests if the user is the community user
     *
     * @todo     decide if this means logged out. I think it usually does.
     * @return bool
     * @category Security-Critical
     */
    public function isCommunityUser()
    {
        return false;
    }

    #endregion 

    /**
     * Gets the approval date of the user
     * @return DateTime|false
     */
    public function getApprovalDate()
    {
        $query = $this->dbObject->prepare(<<<SQL
			SELECT timestamp 
			FROM log 
			WHERE objectid = :userid
				AND objecttype = 'User'
				AND action = 'Approved' 
			ORDER BY id DESC 
			LIMIT 1;
SQL
        );
        $query->execute(array(":userid" => $this->id));

        $data = DateTime::createFromFormat("Y-m-d H:i:s", $query->fetchColumn());
        $query->closeCursor();

        return $data;
    }
}

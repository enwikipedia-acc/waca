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
use stdClass;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;

class OAuthIdentity extends DataObject
{
    #region Fields
    /** @var int */
    private $user;
    /** @var string */
    private $iss;
    /** @var int */
    private $sub;
    /** @var string */
    private $aud;
    /** @var int */
    private $exp;
    /** @var int */
    private $iat;
    /** @var string */
    private $username;
    /** @var int */
    private $editcount;
    /** @var int */
    private $confirmed_email;
    /** @var int */
    private $blocked;
    /** @var string */
    private $registered;
    /** @var int */
    private $checkuser;
    /** @var int */
    private $grantbasic;
    /** @var int */
    private $grantcreateaccount;
    /** @var int */
    private $granthighvolume;
    /** @var int */
    private $grantcreateeditmovepage;
    #endregion

    /**
     * Saves a data object to the database, either updating or inserting a record.
     * @return void
     * @throws Exception
     * @throws OptimisticLockFailedException
     */
    public function save()
    {
        if ($this->isNew()) {
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO oauthidentity (
                    user, iss, sub, aud, exp, iat, username, editcount, confirmed_email, blocked, registered, checkuser, 
                    grantbasic, grantcreateaccount, granthighvolume, grantcreateeditmovepage
                ) VALUES (
                    :user, :iss, :sub, :aud, :exp, :iat, :username, :editcount, :confirmed_email, :blocked, :registered,
                    :checkuser, :grantbasic, :grantcreateaccount, :granthighvolume, :grantcreateeditmovepage
                )
SQL
            );

            $statement->bindValue(':user', $this->user);
            $statement->bindValue(':iss', $this->iss);
            $statement->bindValue(':sub', $this->sub);
            $statement->bindValue(':aud', $this->aud);
            $statement->bindValue(':exp', $this->exp);
            $statement->bindValue(':iat', $this->iat);
            $statement->bindValue(':username', $this->username);
            $statement->bindValue(':editcount', $this->editcount);
            $statement->bindValue(':confirmed_email', $this->confirmed_email);
            $statement->bindValue(':blocked', $this->blocked);
            $statement->bindValue(':registered', $this->registered);
            $statement->bindValue(':checkuser', $this->checkuser);
            $statement->bindValue(':grantbasic', $this->grantbasic);
            $statement->bindValue(':grantcreateaccount', $this->grantcreateaccount);
            $statement->bindValue(':granthighvolume', $this->granthighvolume);
            $statement->bindValue(':grantcreateeditmovepage', $this->grantcreateeditmovepage);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            $statement = $this->dbObject->prepare(<<<SQL
                UPDATE oauthidentity SET
                      iss                     = :iss
                    , sub                     = :sub
                    , aud                     = :aud
                    , exp                     = :exp
                    , iat                     = :iat
                    , username                = :username
                    , editcount               = :editcount
                    , confirmed_email         = :confirmed_email
                    , blocked                 = :blocked
                    , registered              = :registered
                    , checkuser               = :checkuser
                    , grantbasic              = :grantbasic
                    , grantcreateaccount      = :grantcreateaccount
                    , granthighvolume         = :granthighvolume
                    , grantcreateeditmovepage = :grantcreateeditmovepage
                    , updateversion           = updateversion + 1
                WHERE  id = :id AND updateversion = :updateversion
SQL
            );

            $statement->bindValue(':iss', $this->iss);
            $statement->bindValue(':sub', $this->sub);
            $statement->bindValue(':aud', $this->aud);
            $statement->bindValue(':exp', $this->exp);
            $statement->bindValue(':iat', $this->iat);
            $statement->bindValue(':username', $this->username);
            $statement->bindValue(':editcount', $this->editcount);
            $statement->bindValue(':confirmed_email', $this->confirmed_email);
            $statement->bindValue(':blocked', $this->blocked);
            $statement->bindValue(':registered', $this->registered);
            $statement->bindValue(':checkuser', $this->checkuser);
            $statement->bindValue(':grantbasic', $this->grantbasic);
            $statement->bindValue(':grantcreateaccount', $this->grantcreateaccount);
            $statement->bindValue(':granthighvolume', $this->granthighvolume);
            $statement->bindValue(':grantcreateeditmovepage', $this->grantcreateeditmovepage);

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

    #region Properties

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUserId($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getIssuer()
    {
        return $this->iss;
    }

    /**
     * @return int
     */
    public function getSubject()
    {
        return $this->sub;
    }

    /**
     * @return string
     */
    public function getAudience()
    {
        return $this->aud;
    }

    /**
     * @return int
     */
    public function getExpirationTime()
    {
        return $this->exp;
    }

    /**
     * @return int
     */
    public function getIssuedAtTime()
    {
        return $this->iat;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getEditCount()
    {
        return $this->editcount;
    }

    /**
     * @return bool
     */
    public function getConfirmedEmail()
    {
        return $this->confirmed_email == 1;
    }

    /**
     * @return bool
     */
    public function getBlocked()
    {
        return $this->blocked == 1;
    }

    /**
     * @return string
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    public function getRegistrationDate()
    {
        return DateTimeImmutable::createFromFormat('YmdHis', $this->registered)->format('r');
    }

    public function getAccountAge()
    {
        $regDate = DateTimeImmutable::createFromFormat('YmdHis', $this->registered);
        $interval = $regDate->diff(new DateTimeImmutable(), true);

        return $interval->days;
    }

    /**
     * @return bool
     */
    public function getCheckuser()
    {
        return $this->checkuser == 1;
    }

    /**
     * @return bool
     */
    public function getGrantBasic()
    {
        return $this->grantbasic == 1;
    }

    /**
     * @return bool
     */
    public function getGrantCreateAccount()
    {
        return $this->grantcreateaccount == 1;
    }

    /**
     * @return bool
     */
    public function getGrantHighVolume()
    {
        return $this->granthighvolume == 1;
    }

    /**
     * @return bool
     */
    public function getGrantCreateEditMovePage()
    {
        return $this->grantcreateeditmovepage == 1;
    }

    #endregion Properties

    /**
     * Populates the fields of this instance from a provided JSON Web Token
     *
     * @param stdClass $jwt
     */
    public function populate($jwt)
    {
        $this->iss = $jwt->iss;
        $this->sub = $jwt->sub;
        $this->aud = $jwt->aud;
        $this->exp = $jwt->exp;
        $this->iat = $jwt->iat;
        $this->username = $jwt->username;
        $this->editcount = $jwt->editcount;
        $this->confirmed_email = $jwt->confirmed_email ? 1 : 0;
        $this->blocked = $jwt->blocked ? 1 : 0;
        $this->registered = $jwt->registered;

        /*
         * Rights we need:
         *  Account creation
         *      createaccount      => createaccount
         *  Flagged users:
         *      tboverride-account => createaccount
         *      override-antispoof => N/A
         *  Welcome bot:
         *      createtalk         => createeditmovepage
         *      edit               => editpage/editprotected/editmycssjs/editinterface/createmoveeditpage/delete/protect
         *  Would be nice:
         *      apihighlimits      => highvolume
         *      noratelimit        => highvolume
         *
         * Hence, we're requesting these grants:
         *      useoauth (required)
         *      createaccount
         *      createeditmovepage
         *
         * Any antispoof conflicts will still have to be resolved manually using the normal creation form.
         */

        $this->grantbasic = in_array('basic', $jwt->grants) ? 1 : 0;
        $this->grantcreateaccount = in_array('createaccount', $jwt->grants) ? 1 : 0;
        $this->grantcreateeditmovepage = in_array('createeditmovepage', $jwt->grants) ? 1 : 0;
        $this->granthighvolume = in_array('highvolume', $jwt->grants) ? 1 : 0;

        $this->checkuser = in_array('checkuser-log', $jwt->rights) ? 1 : 0;
    }
}

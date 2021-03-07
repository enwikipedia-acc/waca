<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use Waca\DataObjects\Credential;
use Waca\DataObjects\User;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

abstract class CredentialProviderBase implements ICredentialProvider
{
    /**
     * @var PdoDatabase
     */
    private $database;
    /**
     * @var SiteConfiguration
     */
    private $configuration;
    /** @var string */
    private $type;

    /**
     * CredentialProviderBase constructor.
     *
     * @param PdoDatabase       $database
     * @param SiteConfiguration $configuration
     * @param string            $type
     */
    public function __construct(PdoDatabase $database, SiteConfiguration $configuration, $type)
    {
        $this->database = $database;
        $this->configuration = $configuration;
        $this->type = $type;
    }

    /**
     * @param int  $userId
     *
     * @param bool $disabled
     *
     * @return Credential
     */
    protected function getCredentialData($userId, $disabled = false)
    {
        $sql = 'SELECT * FROM credential WHERE type = :t AND user = :u';
        $parameters = array(
            ':u' => $userId,
            ':t' => $this->type
        );

        if ($disabled !== null) {
            $sql .= ' AND disabled = :d';
            $parameters[':d'] = $disabled ? 1 : 0;
        }

        $statement = $this->database->prepare($sql);
        $statement->execute($parameters);

        /** @var Credential $obj */
        $obj = $statement->fetchObject(Credential::class);

        if ($obj === false) {
            return null;
        }

        $obj->setDatabase($this->database);

        $statement->closeCursor();

        return $obj;
    }

    /**
     * @return PdoDatabase
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return SiteConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function deleteCredential(User $user)
    {
        // get this factor
        $statement = $this->database->prepare('SELECT * FROM credential WHERE user = :user AND type = :type');
        $statement->execute(array(':user' => $user->getId(), ':type' => $this->type));
        /** @var Credential $credential */
        $credential = $statement->fetchObject(Credential::class);
        $credential->setDatabase($this->database);
        $statement->closeCursor();

        $stage = $credential->getFactor();

        $statement = $this->database->prepare('SELECT COUNT(*) FROM credential WHERE user = :user AND factor = :factor');
        $statement->execute(array(':user' => $user->getId(), ':factor' => $stage));
        $alternates = $statement->fetchColumn();
        $statement->closeCursor();

        if ($alternates <= 1) {
            // decrement the factor for every stage above this
            $sql = 'UPDATE credential SET factor = factor - 1 WHERE user = :user AND factor > :factor';
            $statement = $this->database->prepare($sql);
            $statement->execute(array(':user' => $user->getId(), ':factor' => $stage));
        }
        else {
            // There are other auth factors at this point. Don't renumber the factors just yet.
        }

        // delete this credential.
        $credential->delete();
    }

    /**
     * @param User $user
     *
     * @return Credential
     */
    protected function createNewCredential(User $user)
    {
        $credential = new Credential();
        $credential->setDatabase($this->getDatabase());
        $credential->setUserId($user->getId());
        $credential->setType($this->type);

        return $credential;
    }

    /**
     * @param int $userId
     *
     * @return bool
     */
    public function userIsEnrolled($userId)
    {
        $cred = $this->getCredentialData($userId);

        return $cred !== null;
    }
}
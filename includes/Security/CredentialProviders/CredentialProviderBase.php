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
     * @param int $userId
     *
     * @return Credential
     */
    protected function getCredentialData($userId)
    {
        $sql = 'SELECT * FROM credential WHERE type = :t AND user = :u AND disabled = 0';

        $statement = $this->database->prepare($sql);
        $statement->execute(array(':u' => $userId, ':t' => $this->type));

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
}
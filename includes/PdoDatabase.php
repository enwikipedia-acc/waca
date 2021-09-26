<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Exception;
use PDO;
use PDOException;
use Waca\Exceptions\EnvironmentException;

class PdoDatabase extends PDO
{
    public const ISOLATION_SERIALIZABLE = 'SERIALIZABLE';
    public const ISOLATION_READ_COMMITTED = 'READ COMMITTED';
    public const ISOLATION_READ_ONLY = 'READ ONLY';

    /**
     * @var PdoDatabase[]
     */
    private static $connections = array();
    /**
     * @var bool True if a transaction is active
     */
    protected $hasActiveTransaction = false;

    /**
     * Unless you're doing low-level work, this is not the function you want.
     *
     * @param string $connectionName
     *
     * @return PdoDatabase
     * @throws Exception
     */
    public static function getDatabaseConnection($connectionName)
    {
        if (!isset(self::$connections[$connectionName])) {
            global $cDatabaseConfig;

            if (!array_key_exists($connectionName, $cDatabaseConfig)) {
                throw new Exception("Database configuration not found for alias $connectionName");
            }

            try {
                $databaseObject = new PdoDatabase(
                    $cDatabaseConfig[$connectionName]["dsrcname"],
                    $cDatabaseConfig[$connectionName]["username"],
                    $cDatabaseConfig[$connectionName]["password"],
                    $cDatabaseConfig[$connectionName]["options"]
                );
            }
            catch (PDOException $ex) {
                // wrap around any potential stack traces which may include passwords
                throw new EnvironmentException("Error connecting to database '$connectionName': " . $ex->getMessage());
            }

            $databaseObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // emulating prepared statements gives a performance boost on MySQL.
            //
            // however, our version of PDO doesn't seem to understand parameter types when emulating
            // the prepared statements, so we're forced to turn this off for now.
            // -- stw 2014-02-11
            $databaseObject->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Set the default transaction mode
            $databaseObject->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;");

            self::$connections[$connectionName] = $databaseObject;
        }

        return self::$connections[$connectionName];
    }

    /**
     * Determines if this connection has a transaction in progress or not
     * @return boolean true if there is a transaction in progress.
     */
    public function hasActiveTransaction()
    {
        return $this->hasActiveTransaction;
    }

    /**
     * Summary of beginTransaction
     * @return bool
     */
    public function beginTransaction(string $isolationLevel = self::ISOLATION_READ_COMMITTED)
    {
        // Override the pre-existing method, which doesn't stop you from
        // starting transactions within transactions - which doesn't work and
        // will throw an exception. This eliminates the need to catch exceptions
        // all over the rest of the code
        if ($this->hasActiveTransaction) {
            return false;
        }
        else {
            $accessMode = 'READ WRITE';

            switch ($isolationLevel) {
                case self::ISOLATION_SERIALIZABLE:
                case self::ISOLATION_READ_COMMITTED:
                    break;
                case self::ISOLATION_READ_ONLY:
                    $isolationLevel = self::ISOLATION_READ_COMMITTED;
                    $accessMode = 'READ ONLY';
                    break;
                default:
                    throw new Exception("Invalid transaction isolation level");
            }

            // set the transaction isolation level for every transaction.
            // string substitution is safe here; values can only be one of the above constants
            parent::exec("SET TRANSACTION ISOLATION LEVEL ${isolationLevel}, ${accessMode};");

            // start a new transaction, and return whether the start was successful
            $this->hasActiveTransaction = parent::beginTransaction();

            return $this->hasActiveTransaction;
        }
    }

    /**
     * Commits the active transaction
     */
    public function commit()
    {
        if ($this->hasActiveTransaction) {
            parent::commit();
            $this->hasActiveTransaction = false;
        }
    }

    /**
     * Rolls back a transaction
     */
    public function rollBack()
    {
        if ($this->hasActiveTransaction) {
            parent::rollback();
            $this->hasActiveTransaction = false;
        }
    }
}

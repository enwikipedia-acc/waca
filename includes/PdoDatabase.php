<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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

    private static PdoDatabase $connection;
    /**
     * @var bool True if a transaction is active
     */
    protected bool $hasActiveTransaction = false;

    /**
     * Unless you're doing low-level work, this is not the function you want.
     *
     * @throws Exception
     */
    public static function getDatabaseConnection(SiteConfiguration $configuration): PdoDatabase
    {
        if (!isset(self::$connection)) {
            $dbConfig = $configuration->getDatabaseConfig();

            try {
                $databaseObject = new PdoDatabase(
                    $dbConfig['datasource'],
                    $dbConfig['username'],
                    $dbConfig['password'],
                    [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_520_ci']
                );
            }
            catch (PDOException $ex) {
                // wrap around any potential stack traces which may include passwords
                throw new EnvironmentException('Error connecting to database: ' . $ex->getMessage());
            }

            $databaseObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // emulating prepared statements gives a performance boost on MySQL.
            //
            // however, our version of PDO doesn't seem to understand parameter types when emulating
            // the prepared statements, so we're forced to turn this off for now.
            // -- stw 2014-02-11
            //
            // and that's not the only problem with emulated prepares. We've now got code that relies
            // on real prepares.
            // -- stw 2023-09-30
            $databaseObject->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Set the default transaction mode
            $databaseObject->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;");

            self::$connection = $databaseObject;
        }

        return self::$connection;
    }

    /**
     * Determines if this connection has a transaction in progress or not
     * @return boolean true if there is a transaction in progress.
     */
    public function hasActiveTransaction(): bool
    {
        return $this->hasActiveTransaction;
    }

    public function beginTransaction(string $isolationLevel = self::ISOLATION_READ_COMMITTED): bool
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
    public function commit(): void
    {
        if ($this->hasActiveTransaction) {
            parent::commit();
            $this->hasActiveTransaction = false;
        }
    }

    /**
     * Rolls back a transaction
     */
    public function rollBack(): void
    {
        if ($this->hasActiveTransaction) {
            parent::rollback();
            $this->hasActiveTransaction = false;
        }
    }
}

<?php

/**
 * @param string $db
 * @return PdoDatabase
 * @throws Exception
 */
function gGetDb($db = "acc")
{
	return PdoDatabase::getDatabaseConnection($db);
}

class PdoDatabase extends PDO
{
	/**
	 * @var PdoDatabase[]
	 */
	private static $connections = array();

	/**
	 * @var bool True if a transaction is active
	 */
	protected $hasActiveTransaction = false;
    
	/**
	 * Summary of $queryLogStatement
	 * @var PDOStatement
	 */
	private $queryLogStatement;

	/**
	 * @param string $connectionName
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
				throw new Exception("Error connecting to database '$connectionName': " . $ex->getMessage());
			}

			$databaseObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// emulating prepared statements gives a performance boost on MySQL.
			//
			// however, our version of PDO doesn't seem to understand parameter types when emulating
			// the prepared statements, so we're forced to turn this off for now.
			// -- stw 2014-02-11
			$databaseObject->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

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
	public function beginTransaction()
	{
		// Override the pre-existing method, which doesn't stop you from
		// starting transactions within transactions - which doesn't work and
		// will throw an exception. This eliminates the need to catch exceptions
		// all over the rest of the code
		if ($this->hasActiveTransaction) {
			return false;
		}
		else {
			// set the transaction isolation level for every transaction.
			$this->exec("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;");

			// start a new transaction, and return whether or not the start was
			// successful
			$this->hasActiveTransaction = parent::beginTransaction();
			return $this->hasActiveTransaction;
		}
	}

	/**
	 * Commits the active transaction
	 */
	public function commit()
	{
		parent::commit();
		$this->hasActiveTransaction = false;
	}

	/**
	 * Rolls back a transaction
	 */
	public function rollBack()
	{
		parent::rollback();
		$this->hasActiveTransaction = false;
	}

	/**
	 * Summary of transactionally
	 * @param Closure $method 
	 */
	public function transactionally($method)
	{
		if (!$this->beginTransaction()) {
			BootstrapSkin::displayAlertBox("Error starting database transaction.", "alert-error", "Database transaction error", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

		try {
			$method();

			$this->commit();
		}
		catch (TransactionException $ex) {
			$this->rollBack();

			BootstrapSkin::displayAlertBox($ex->getMessage(), $ex->getAlertType(), $ex->getTitle(), true, false);

			// TODO: yuk.
			if (defined("PUBLICMODE")) {
				BootstrapSkin::displayPublicFooter();
			}
			else {
				BootstrapSkin::displayInternalFooter();
			}

			die();
		}
	}

	/**
	 * Prepares a statement for execution.
	 * @param string $statement 
	 * @param array $driver_options 
	 * @return PDOStatement
	 */
	public function prepare($statement, $driver_options = array())
	{
		global $enableQueryLog;
		if ($enableQueryLog) {
			try {
				if ($this->queryLogStatement === null) {
					$this->queryLogStatement = 
						parent::prepare(<<<SQL
							INSERT INTO applicationlog (source, message, stack, request, request_ts) 
							VALUES (:source, :message, :stack, :request, :rqts);
SQL
						);
				}

				$this->queryLogStatement->execute(
					array(
						":source" => "QueryLog",
						":message" => $statement,
						":stack" => DebugHelper::getBacktrace(),
						":request" => $_SERVER["REQUEST_URI"],
						":rqts" => $_SERVER["REQUEST_TIME_FLOAT"],
					)
				);
			}
			catch (Exception $ex) {
				trigger_error("Error logging query. Disabling for this request. " . $ex->getMessage(), E_USER_NOTICE);
				$enableQueryLog = false;
			}
		}
        
		return parent::prepare($statement, $driver_options);   
	}
}

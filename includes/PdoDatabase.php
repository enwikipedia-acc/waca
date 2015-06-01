<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

function gGetDb($db = "acc")
{
	global $accdbobjects;
	if( ! is_array( $accdbobjects ) ) {
		$accdbobjects = array();
	}

	if( ! isset( $accdbobjects[ $db ] ) ) {
		global $cDatabaseConfig;

		if(! array_key_exists( $db, $cDatabaseConfig ) ) {
			trigger_error( "Database configuration not found for alias $db" );
			die();
		}

		try {
			$accdbobject = new PdoDatabase(
				$cDatabaseConfig[ $db ][ "dsrcname" ],
				$cDatabaseConfig[ $db ][ "username" ],
				$cDatabaseConfig[ $db ][ "password" ]
			);
		}
		catch (PDOException $ex) {
			// wrap around any potential stack traces which may include passwords
			throw new Exception("Error connectiong to database '$db': " . $ex->getMessage());
		}

		$accdbobject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// emulating prepared statements gives a performance boost on MySQL.
		//
		// however, our version of PDO doesn't seem to understand parameter types when emulating
		// the prepared statements, so we're forced to turn this off for now.
		// -- stw 2014-02-11
		$accdbobject->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		$accdbobjects[ $db ] = $accdbobject;
	}

	return $accdbobjects[ $db ];
}

$accdbobjects = array();

class PdoDatabase extends PDO
{
	protected $hasActiveTransaction = false;
    
	/**
	 * Summary of $queryLogStatement
	 * @var PDOStatement
	 */
	private $queryLogStatement;

	/**
	 * Determines if this connection has a transaction in progress or not
	 * @return boolean true if there is a transaction in progress.
	 */
	public function hasActiveTransaction()
	{
		return $this->hasActiveTransaction;
	}

	public function beginTransaction()
	{
		// Override the pre-existing method, which doesn't stop you from
		// starting transactions within transactions - which doesn't work and
		// will throw an exception. This elimiates the need to catch exeptions
		// all over the rest of the code
		if ( $this->hasActiveTransaction ) {
			return false;
		}
		else {
			// set the transaction isolation level for every transaction.
			$this->exec( "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;" );

			// start a new transaction, and return whether or not the start was
			// successful
			$this->hasActiveTransaction = parent::beginTransaction();
			return $this->hasActiveTransaction;
		}
	}

	public function commit()
	{
		parent::commit();
		$this->hasActiveTransaction = false;
	}

	public function rollBack()
	{
		parent::rollback();
		$this->hasActiveTransaction = false;
	}

	public function transactionally($method)
	{
		if( ! $this->beginTransaction() ) {
			BootstrapSkin::displayAlertBox("Error starting database transaction.", "alert-error", "Database transaction error", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

		try {
			$method();

			$this->commit();
		}
		catch(TransactionException $ex) {
			$this->rollBack();

			BootstrapSkin::displayAlertBox($ex->getMessage(), $ex->getAlertType(), $ex->getTitle(), true, false);

			// TODO: yuk.
			if(defined("PUBLICMODE")) {
				BootstrapSkin::displayPublicFooter();
			}
			else {
				BootstrapSkin::displayInternalFooter();
			}

			die();
		}
	}

	public function prepare($statement, $driver_options = array())
	{
		global $enableQueryLog;
		if($enableQueryLog) {
			try {
				if($this->queryLogStatement === null) {
					$this->queryLogStatement = 
						parent::prepare("INSERT INTO applicationlog (source, message, stack) VALUES (:source, :message, :stack);");
				}
                            
				$this->queryLogStatement->execute(
					array(
						":source" => "QueryLog",
						":message" => $statement,
						":stack" => DebugHelper::getBacktrace()
					)
				);
			}
			catch(Exception $ex) {
				trigger_error("Error logging query. Disabling for this request. " . $ex->getMessage(), E_USER_NOTICE);
				$enableQueryLog = false;
			}
		}
        
		return parent::prepare($statement, $driver_options);   
	}
}

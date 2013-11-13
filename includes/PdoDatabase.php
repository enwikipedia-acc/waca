<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

function gGetDb($db = "acc") {
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
	
		$accdbobject = new PdoDatabase(
			$cDatabaseConfig[ $db ][ "dsrcname" ],
			$cDatabaseConfig[ $db ][ "username" ],
			$cDatabaseConfig[ $db ][ "password" ]
		);
        
        $accdbobject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $accdbobjects[ $db ] = $accdbobject;
	}
	return $accdbobject;
}

$accdbobjects = array();

class PdoDatabase extends PDO {
	protected $hasActiveTransaction = false;

	/**
	 * Determines if this connection has a transaction in progress or not
	 * @return true if there is a transaction in progress.
	 */
	public function hasActiveTransaction() {
		return $this->hasActiveTransaction;
	}

	public function beginTransaction() {
		// Override the pre-existing method, which doesn't stop you from 
		// starting transactions within transactions - which doesn't work and 
		// will throw an exception. This elimiates the need to catch exeptions
		// all over the rest of the code
		if ( $this->hasActiveTransaction ) {
			return false;
		} else {
			// set the transaction isolation level for every transaction.
			$this->exec( "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;" );
			
			// start a new transaction, and return whether or not the start was
			// successful
			$this->hasActiveTransaction = parent::beginTransaction();
			return $this->hasActiveTransaction;
		}
	}

	public function commit() {
		parent::commit();
		$this->hasActiveTransaction = false;
	}

	public function rollBack() {
		parent::rollback();
		$this->hasActiveTransaction = false;
	}
}
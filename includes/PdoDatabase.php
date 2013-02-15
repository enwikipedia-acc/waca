<?php

function gGetDb($db = "acc") {
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
		if ( $this->hasActiveTransaction ) {
			return false;
		} else {
			$this->exec( "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;" );
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
<?php

function gGetDb() {
	if( $accdbobject === null ) {
		global 
			$toolserver_host, 
			$toolserver_database, 
			$toolserver_username, 
			$toolserver_password;
	
		$accdbobject = new PdoDatabase(
			"mysql:host=".$toolserver_host.";dbname=".$toolserver_database,
			$toolserver_username,
			$toolserver_password
		);
	}
	
	return $accdbobject;
}

$accdbobject = null;

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
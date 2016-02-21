<?php

namespace Waca\ConsoleTasks;

use TransactionException;
use Waca\Tasks\ConsoleTaskBase;

class ClearOldDataTask extends ConsoleTaskBase
{
	public function execute()
	{
		$dataClearInterval = $this->getSiteConfiguration()->getDataClearInterval();

		$query = $this->getDatabase()->prepare(<<<SQL
UPDATE request
SET ip = :ip, forwardedip = null, email = :mail, useragent = ''
WHERE date < DATE_SUB(curdate(), INTERVAL {$dataClearInterval});
SQL
		);

		$success = $query->execute(array(
			":ip"   => $this->getSiteConfiguration()->getDataClearIp(),
			":mail" => $this->getSiteConfiguration()->getDataClearEmail(),
		));

		if (!$success) {
			throw new TransactionException("Error in transaction: Could not clear data.");
		}

	}
}
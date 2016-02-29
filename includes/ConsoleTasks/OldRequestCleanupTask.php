<?php

namespace Waca\ConsoleTasks;

use Waca\Tasks\ConsoleTaskBase;

class OldRequestCleanupTask extends ConsoleTaskBase
{
	private $expiryTime;

	/**
	 * OldRequestCleanupTask constructor.
	 */
	public function __construct()
	{
		$this->expiryTime = $this->getSiteConfiguration()->getEmailConfirmationExpiryDays();
	}

	public function execute()
	{
		$statement = $this->getDatabase()->prepare(<<<SQL
            DELETE FROM request
            WHERE
                date < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :expiry DAY)
                AND emailconfirm != 'Confirmed'
                AND emailconfirm != '';
SQL
		);

		$statement->bindValue(':expiry', $this->expiryTime);
		$statement->execute();
	}
}
<?php

namespace Waca\ConsoleTasks;

use Waca\IdentificationVerifier;
use Waca\Tasks\ConsoleTaskBase;

class ClearExpiredIdentificationData extends ConsoleTaskBase
{
	/**
	 * @return void
	 */
	public function execute()
	{
		IdentificationVerifier::clearExpiredCacheEntries($this->getSiteConfiguration(), $this->getDatabase());
	}
}

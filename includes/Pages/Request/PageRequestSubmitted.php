<?php

namespace Waca\Pages\Request;

use Waca\Tasks\PublicInterfacePageBase;

class PageRequestSubmitted extends PublicInterfacePageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		$this->setTemplate('request/email-confirmed.tpl');
	}
}
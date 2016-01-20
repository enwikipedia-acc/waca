<?php
namespace Waca\Pages;

use Waca\PageBase;

class PageMain extends PageBase
{
	/**
	 * Main function for this page, when no actions are called.
	 */
	protected function main()
	{
		ob_end_clean();
		// TODO: Implement main() method.
		phpinfo();

		// throw new \Exception("Not implemented yet!");
	}
}
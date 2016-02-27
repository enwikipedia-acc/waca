<?php

namespace Waca\API;

use DOMElement;
use Waca\Tasks\IRoutedTask;

/**
 * API Action interface
 */
interface IApiAction extends IRoutedTask
{
	public function executeApiAction(DOMElement $apiDocument);

	public function runApiPage();
}

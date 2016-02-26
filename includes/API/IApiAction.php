<?php

namespace Waca\API;

use DOMElement;

/**
 * API Action interface
 */
interface IApiAction
{
	public function executeApiAction(DOMElement $apiDocument);

	public function runApiPage();
}

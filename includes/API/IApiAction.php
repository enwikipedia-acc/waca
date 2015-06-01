<?php

namespace Waca\API;

/**
 * API Action interface
 */
interface IApiAction
{

	public function execute(\DOMElement $apiDocument);

	public function run();
}

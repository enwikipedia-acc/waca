<?php

namespace Waca\API;

use DOMElement;
use DOMNode;
use Waca\Tasks\IRoutedTask;

/**
 * API Action interface
 */
interface IApiAction extends IRoutedTask
{
	/**
	 * Method that runs API action
	 *
	 * @param DOMElement $apiDocument
	 *
	 * @return DOMElement The modified API document
	 */
	public function executeApiAction(DOMElement $apiDocument);

	/**
	 * @return string the XML, or false if an error occurred.
	 */
	public function runApiPage();
}

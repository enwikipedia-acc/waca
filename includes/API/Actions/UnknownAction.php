<?php

namespace Waca\API\Actions;

use DOMElement;
use Waca\API\IApiAction;

/**
 * API Help action
 */
class UnknownAction extends HelpAction implements IApiAction
{
	public function executeApiAction(DOMElement $apiDocument)
	{
		$errorText = "Unknown API action specified.";
		$errorNode = $this->document->createElement("error", $errorText);
		$apiDocument->appendChild($errorNode);

		$helpElement = $this->getHelpElement();
		$apiDocument->appendChild($helpElement);

		return $apiDocument;
	}
}

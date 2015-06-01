<?php

namespace Waca\API\Actions;

use Waca\API\Api as Api;
use Waca\API\ApiActionBase as ApiActionBase;
use Waca\API\IApiAction as IApiAction;

/**
 * API Help action
 */
class HelpAction extends ApiActionBase implements IApiAction
{
	public function execute(\DOMElement $apiDocument)
	{
		$helpElement = $this->getHelpElement();
		$apiDocument->appendChild($helpElement);

		return $apiDocument;
	}

	/**
	 * Gets the help information
	 * @return \DOMNode
	 */
	protected function getHelpElement()
	{
		$helpInfo = "Help info goes here!";

		$help = $this->document->createElement("help");
		$helptext = $this->document->createElement("info", $helpInfo);
		$helpactions = $this->document->createElement("actions");

		foreach (Api::getActionList() as $action) {
			$actionElement = $this->document->createElement("action", $action);
			$helpactions->appendChild($actionElement);
		}

		$help->appendChild($helptext);
		$help->appendChild($helpactions);

		return $help;
	}
}

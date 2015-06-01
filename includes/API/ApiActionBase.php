<?php

namespace Waca\API;

/**
 * ApiActionBase
 */
abstract class ApiActionBase implements IApiAction
{
	/**
	 * API result document
	 * @var DomDocument
	 */
	protected $document;

	public function __construct()
	{
		$this->document = new \DomDocument('1.0');
	}

	/**
	 * Method that runs API action
	 */
	abstract public function execute(\DOMElement $apiDocument);

	public function run()
	{

		$apiDocument = $this->document->createElement("api");

		try {
			$apiDocument = $this->execute($apiDocument);
		}
		catch (ApiException $ex) {
			$exception = $this->document->createElement("error");
			$exception->setAttribute("message", $ex->getMessage());
			$apiDocument->appendChild($exception);
		}

		$this->document->appendChild($apiDocument);

		return $this->document->saveXml();
	}
}

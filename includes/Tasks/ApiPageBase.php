<?php

namespace Waca\Tasks;

use DomDocument;
use DOMElement;
use Waca\API\ApiException;
use Waca\API\IApiAction;
use Waca\WebRequest;

abstract class ApiPageBase extends TaskBase implements IRoutedTask, IApiAction
{
	/**
	 * API result document
	 * @var DomDocument
	 */
	protected $document;

	public function __construct()
	{
		$this->document = new DomDocument('1.0');
	}

	final public function execute()
	{
		$this->main();
	}

	/**
	 * @param string $routeName
	 */
	public function setRoute($routeName)
	{
		// no-op
	}

	public function getRouteName()
	{
		return 'main';
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	final protected function main()
	{
		header("Content-Type: text/xml");

		// javascript access control
		$httpOrigin = WebRequest::origin();

		if ($httpOrigin !== null) {
			$CORSallowed = $this->getSiteConfiguration()->getCrossOriginResourceSharingHosts();

			if (in_array($httpOrigin, $CORSallowed)) {
				header("Access-Control-Allow-Origin: " . $httpOrigin);
			}
		}

		$responseData = $this->runApiPage();

		ob_end_clean();
		print($responseData);
		ob_start();
	}

	/**
	 * Method that runs API action
	 *
	 * @param DOMElement $apiDocument
	 *
	 * @return
	 */
	abstract public function executeApiAction(DOMElement $apiDocument);

	final public function runApiPage()
	{

		$apiDocument = $this->document->createElement("api");

		try {
			$apiDocument = $this->executeApiAction($apiDocument);
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
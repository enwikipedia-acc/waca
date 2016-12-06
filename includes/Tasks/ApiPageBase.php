<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tasks;

use DOMDocument;
use DOMElement;
use Waca\API\ApiException;
use Waca\API\IApiAction;
use Waca\WebRequest;

abstract class ApiPageBase extends TaskBase implements IRoutedTask, IApiAction
{
    /**
     * API result document
     * @var DOMDocument
     */
    protected $document;

    public function __construct()
    {
        $this->document = new DOMDocument('1.0');
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

    /**
     * @return string
     */
    public function getRouteName()
    {
        return 'main';
    }

    /**
     * Main function for this page, when no specific actions are called.
     *
     * @throws ApiException
     * @return void
     */
    final protected function main()
    {
        if (headers_sent()) {
            throw new ApiException('Headers have already been sent - this indicates a bug in the application!');
        }

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
     * @return DOMElement
     */
    abstract public function executeApiAction(DOMElement $apiDocument);

    /**
     * @return string
     */
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

        return $this->document->saveXML();
    }
}
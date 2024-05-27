<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tasks;

use DOMDocument;
use DOMElement;
use Waca\API\ApiException;
use Waca\API\IXmlApiAction;
use Waca\WebRequest;


abstract class XmlApiPageBase extends ApiPageBase implements IXmlApiAction
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
            /** @noinspection PhpRedundantCatchClauseInspection */
        catch (ApiException $ex) {
            $exception = $this->document->createElement("error");
            $exception->setAttribute("message", $ex->getMessage());
            $apiDocument->appendChild($exception);
        }

        $this->document->appendChild($apiDocument);

        return $this->document->saveXML();
    }
}

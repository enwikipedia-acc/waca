<?php

namespace Waca\API\Actions;

use Waca\API\IApiAction as IApiAction;

/**
 * API Help action
 */
class UnknownAction extends HelpAction implements IApiAction
{
    public function execute(\DOMElement $doc_api)
    {
        $errorText = "Unknown API action specified.";
        $errorNode = $this->document->createElement("error", $errorText);
        $doc_api->appendChild($errorNode);
        
        $helpElement = $this->getHelpElement();
        $doc_api->appendChild($helpElement);
        
        return $doc_api;
    }
}

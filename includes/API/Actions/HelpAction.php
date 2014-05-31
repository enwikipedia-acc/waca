<?php

namespace Waca\API\Actions;

use Waca\API\ApiActionBase as ApiActionBase;
use Waca\API\IApiAction as IApiAction;

use Waca\API\Api as Api;

/**
 * API Help action
 */
class HelpAction extends ApiActionBase implements IApiAction
{
    public function execute(\DOMElement $doc_api)
    {
        $helpElement = $this->getHelpElement();
        $doc_api->appendChild($helpElement);
        
        return $doc_api;
    }
    
    /**
     * Gets the help information
     * @return DOMElement
     */
    protected function getHelpElement()
    {
        $helpInfo = "Help info goes here!";
        
        $help = $this->document->createElement("help");
        $helptext = $this->document->createElement("info", $helpInfo);
        $helpactions = $this->document->createElement("actions");
        
        foreach (Api::getActionList() as $action)
        {
            $actionElement = $this->document->createElement("action", $action);
            $helpactions->appendChild($actionElement);
        }
        
        $help->appendChild($helptext);
        $help->appendChild($helpactions);
        
        return $help;
    }
}

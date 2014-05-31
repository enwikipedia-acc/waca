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
    public abstract function execute(\DOMElement $doc_api);
    
    
    public function run()
    {
        
        $doc_api = $this->document->createElement("api");
        
        try
        {
            $doc_api = $this->execute($doc_api);
        }
        catch(ApiException $ex)
        {
            $exception = $this->document->createElement("error");
            $exception->setAttribute("message", $ex->getMessage());
            $doc_api->appendChild($exception);
        }

        $this->document->appendChild($doc_api);
        
        return $this->document->saveXml();
    }
}

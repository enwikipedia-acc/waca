<?php

namespace Waca\API;

/**
 * API Action interface
 */
interface IApiAction
{
    
    public function execute(\DOMElement $doc_api);
    
    
    public function run();
}

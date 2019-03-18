<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\API\Actions;

use DOMElement;
use Waca\API\IApiAction;
use Waca\Router\ApiRequestRouter;
use Waca\Tasks\ApiPageBase;

/**
 * API Help action
 */
class HelpAction extends ApiPageBase implements IApiAction
{
    public function executeApiAction(DOMElement $apiDocument)
    {
        $helpElement = $this->getHelpElement();
        $apiDocument->appendChild($helpElement);

        return $apiDocument;
    }

    /**
     * Gets the help information
     * @return DOMElement
     */
    protected function getHelpElement()
    {
		$helpInfo = "API help can be found at https://github.com/enwikipedia-acc/waca/wiki/API";

        $help = $this->document->createElement("help");
        $helptext = $this->document->createElement("info", $helpInfo);
        $helpactions = $this->document->createElement("actions");

        foreach (ApiRequestRouter::getActionList() as $action) {
            $actionElement = $this->document->createElement("action", $action);
            $helpactions->appendChild($actionElement);
        }

        $help->appendChild($helptext);
        $help->appendChild($helpactions);

        return $help;
    }
}

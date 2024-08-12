<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\API\Actions;

use DOMElement;
use Waca\API\IXmlApiAction;

/**
 * API Help action
 */
class UnknownAction extends HelpAction implements IXmlApiAction
{
    public function executeApiAction(DOMElement $apiDocument)
    {
        $errorText = "Unknown API action specified.";
        $errorNode = $this->document->createElement("error", $errorText);
        $apiDocument->appendChild($errorNode);

        $helpElement = $this->getHelpElement();
        $apiDocument->appendChild($helpElement);

        return $apiDocument;
    }
}

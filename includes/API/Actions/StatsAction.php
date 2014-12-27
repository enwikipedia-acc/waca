<?php

namespace Waca\API\Actions;

use Waca\API\ApiActionBase as ApiActionBase;
use Waca\API\ApiException as ApiException;
use Waca\API\IApiAction as IApiAction;

/**
 * API Count action
 */
class StatsAction extends ApiActionBase implements IApiAction
{
    /**
     * The target user
     * @var User $user
     */
    private $user;

    /**
     * The datbase
     * @var PdoDatabase $database
     */
    private $database;

    public function execute(\DOMElement $apiDocument)
    {
        $username = isset( $_GET['user'] ) ? trim($_GET['user']) : '';
        if($username == '')
        {
            throw new ApiException("Please specify a username");
        }

        $userElement = $this->document->createElement("user");
        $userElement->setAttribute("username", $username);
        $apiDocument->appendChild($userElement);

        $this->database = gGetDb();

        $this->user = \User::getByUsername($username, $this->database);

        if($this->user === false)
        {
            $userElement->setAttribute("missing", "true");
            return $apiDocument;
        }

        $userElement->setAttribute("status", $this->user->getStatus());
        $userElement->setAttribute("lastactive", $this->user->getLastActive());
        $userElement->setAttribute("welcome_template", $this->user->getWelcomeTemplate());
        $userElement->setAttribute("onwikiname", $this->user->getOnWikiName());
        $userElement->setAttribute("oauth", $this->user->isOAuthLinked() ? "true" : "false");

        return $apiDocument;
    }
}

<?php

namespace Waca\API\Actions;

use Waca\API\ApiActionBase as ApiActionBase;
use Waca\API\ApiException as ApiException;
use Waca\API\IApiAction as IApiAction;

use \PdoDatabase as PdoDatabase;
use \User as User;

/**
 * API Count action
 */
class StatsAction extends ApiActionBase implements IApiAction
{
	/**
	 * The target user
	 * @var \User $user
	 */
	private $user;

	/**
	 * The database
	 * @var \PdoDatabase $database
	 */
	private $database;

	/**
	 * Summary of execute
	 * @param \DOMElement $apiDocument
	 * @return \DOMElement
	 * @throws ApiException
	 * @throws \Exception
	 */
	public function execute(\DOMElement $apiDocument)
	{
		$username = isset($_GET['user']) ? trim($_GET['user']) : '';
		$wikiusername = isset($_GET['wikiuser']) ? trim($_GET['wikiuser']) : '';

		if ($username === '' && $wikiusername === '') {
			throw new ApiException("Please specify a username using either user or wikiuser parameters.");
		}

		$userElement = $this->document->createElement("user");
		$apiDocument->appendChild($userElement);

		$this->database = gGetDb();

		if ($username !== '') {
			$this->user = \User::getByUsername($username, $this->database);
		}
		else {
			$this->user = \User::getByOnWikiUsername($wikiusername, $this->database);
		}

		if ($this->user === false) {
			$userElement->setAttribute("missing", "true");
			return $apiDocument;
		}

		$userElement->setAttribute("username", $this->user->getUsername());
		$userElement->setAttribute("status", $this->user->getStatus());
		$userElement->setAttribute("lastactive", $this->user->getLastActive());
		$userElement->setAttribute("welcome_template", $this->user->getWelcomeTemplate());
		$userElement->setAttribute("onwikiname", $this->user->getOnWikiName());
		$userElement->setAttribute("oauth", $this->user->isOAuthLinked() ? "true" : "false");

		return $apiDocument;
	}
}

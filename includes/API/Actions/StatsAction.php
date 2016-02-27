<?php

namespace Waca\API\Actions;

use User;
use Waca\API\ApiException;
use Waca\API\IApiAction;
use Waca\Tasks\ApiPageBase;
use Waca\WebRequest;

/**
 * API Count action
 */
class StatsAction extends ApiPageBase implements IApiAction
{
	/**
	 * The target user
	 * @var User $user
	 */
	private $user;

	/**
	 * Summary of execute
	 *
	 * @param \DOMElement $apiDocument
	 *
	 * @return \DOMElement
	 * @throws ApiException
	 * @throws \Exception
	 */
	public function executeApiAction(\DOMElement $apiDocument)
	{
		$username = WebRequest::getString('user');
		$wikiusername = WebRequest::getString('wikiuser');

		if ($username === null && $wikiusername === null) {
			throw new ApiException("Please specify a username using either user or wikiuser parameters.");
		}

		$userElement = $this->document->createElement("user");
		$apiDocument->appendChild($userElement);

		if ($username !== null) {
			$user = User::getByUsername($username, $this->getDatabase());
		}
		else {
			$user = User::getByOnWikiUsername($wikiusername, $this->getDatabase());
		}

		if ($user === false) {
			$userElement->setAttribute("missing", "true");

			return $apiDocument;
		}

		$this->user = $user;

		$userElement->setAttribute("username", $this->user->getUsername());
		$userElement->setAttribute("status", $this->user->getStatus());
		$userElement->setAttribute("lastactive", $this->user->getLastActive());
		$userElement->setAttribute("welcome_template", $this->user->getWelcomeTemplate());
		$userElement->setAttribute("onwikiname", $this->user->getOnWikiName());
		$userElement->setAttribute("oauth", $this->user->isOAuthLinked() ? "true" : "false");

		return $apiDocument;
	}
}

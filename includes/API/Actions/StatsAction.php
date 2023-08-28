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
use Exception;
use Waca\API\ApiException;
use Waca\API\IXmlApiAction;
use Waca\DataObjects\User;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\PreferenceManager;
use Waca\Tasks\XmlApiPageBase;
use Waca\WebRequest;

/**
 * API Count action
 */
class StatsAction extends XmlApiPageBase implements IXmlApiAction
{
    /**
     * Summary of execute
     *
     * @param DOMElement $apiDocument
     *
     * @return DOMElement
     * @throws ApiException
     * @throws Exception
     */
    public function executeApiAction(DOMElement $apiDocument)
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

        $oauth = new OAuthUserHelper($user, $this->getDatabase(), $this->getOAuthProtocolHelper(),
            $this->getSiteConfiguration());

        // FIXME: domains
        $prefs = new PreferenceManager($this->getDatabase(), $user->getId(), 1);

        $userElement->setAttribute("username", $user->getUsername());
        $userElement->setAttribute("status", $user->getStatus());
        $userElement->setAttribute("lastactive", $user->getLastActive());
        $userElement->setAttribute("welcome_template", $prefs->getPreference(PreferenceManager::PREF_WELCOMETEMPLATE));
        $userElement->setAttribute("onwikiname", $user->getOnWikiName());
        $userElement->setAttribute("oauth", $oauth->isFullyLinked() ? "true" : "false");

        return $apiDocument;
    }
}

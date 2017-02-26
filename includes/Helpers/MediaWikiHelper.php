<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Exception;
use Waca\Exceptions\MediaWikiApiException;
use Waca\SiteConfiguration;

class MediaWikiHelper
{
    /**
     * @var OAuthUserHelper
     */
    private $userHelper;
    /**
     * @var SiteConfiguration
     */
    private $siteConfiguration;

    /**
     * MediaWikiHelper constructor.
     *
     * @param OAuthUserHelper   $userHelper
     * @param SiteConfiguration $siteConfiguration
     */
    public function __construct(OAuthUserHelper $userHelper, SiteConfiguration $siteConfiguration)
    {
        $this->userHelper = $userHelper;
        $this->siteConfiguration = $siteConfiguration;
    }

    /**
     * @todo handle override antispoof and titleblacklist issues
     *
     * @param string $username
     * @param string $emailAddress
     * @param string $reason
     *
     * @throws Exception
     * @throws MediaWikiApiException
     */
    public function createAccount($username, $emailAddress, $reason)
    {
        // get token
        $tokenParams = array(
            'action' => 'query',
            'meta'   => 'tokens',
            'type'   => 'createaccount',
        );

        $response = $this->userHelper->doApiCall($tokenParams, 'POST');
        $token = $response->query->tokens->createaccounttoken;

        $callback = $this->siteConfiguration->getBaseUrl() . '/internal.php/oauth/createCallback';

        $checkboxFields = array();
        $requiredFields = array();
        $this->getCreationFieldData($requiredFields, $checkboxFields);

        $apiCallData = array(
            'action'              => 'createaccount',
            'createreturnurl'     => $callback,
            'createtoken'         => $token,
            'createmessageformat' => 'html',
        );

        $createParams = array_fill_keys($requiredFields, '') + $apiCallData;

        $createParams['username'] = $username;
        $createParams['mailpassword'] = true;
        $createParams['email'] = $emailAddress;
        $createParams['reason'] = $reason;

        $createResponse = $this->userHelper->doApiCall($createParams, 'POST');

        if (isset($createResponse->error)) {
            throw new MediaWikiApiException($response->error->code . ': ' . $response->error->info);
        }

        if (!isset($createResponse->createaccount) || !isset($createResponse->createaccount->status)) {
            throw new MediaWikiApiException('Unknown error creating account');
        }

        if ($createResponse->createaccount->status === 'FAIL') {
            throw new MediaWikiApiException($createResponse->createaccount->message);
        }

        if ($createResponse->createaccout->status === 'PASS') {
            // success!
            return;
        }

        throw new Exception('Something happened. Don\'t know what.');
    }

    /**
     * @param string $username
     * @param string $title
     * @param string $message
     * @param bool   $createOnly
     *
     * @throws MediaWikiApiException
     */
    public function addTalkPageMessage($username, $title, $summary, $message, $createOnly = true)
    {
        // get token
        $tokenParams = array(
            'action' => 'query',
            'meta'   => 'tokens',
            'type'   => 'csrf',
        );

        $response = $this->userHelper->doApiCall($tokenParams, 'POST');

        if (isset($response->error)) {
            throw new MediaWikiApiException($response->error->code . ': ' . $response->error->info);
        }

        $token = $response->query->tokens->csrftoken;

        if ($token === null) {
            throw new MediaWikiApiException('Edit token could not be acquired');
        }

        $editParameters = array(
            'action'       => 'edit',
            'title'        => 'User talk:' . $username,
            'section'      => 'new',
            'sectiontitle' => $title,
            'summary'      => $summary,
            'text'         => $message,
            'token'        => $token,
        );

        if ($createOnly) {
            $editParameters['createonly'] = true;
        }

        $response = $this->userHelper->doApiCall($editParameters, 'POST');

        if (!isset($response->edit)) {
            if (isset($response->error)) {
                throw new MediaWikiApiException($response->error->code . ': ' . $response->error->info);
            }

            throw new MediaWikiApiException('Unknown error encountered during editing.');
        }

        $editResponse = $response->edit;
        if ($editResponse->result === "Success") {
            return;
        }

        throw new MediaWikiApiException('Edit status unsuccessful: ' . $editResponse->result);
    }

    public function getCreationFieldData(&$requiredFields, &$czechboxFields)
    {
        // get token
        $params = array(
            'action'         => 'query',
            'meta'           => 'authmanagerinfo',
            'amirequestsfor' => 'create',
        );

        $response = $this->userHelper->doApiCall($params, 'GET');

        if (isset($response->error)) {
            throw new MediaWikiApiException($response->error->code . ': ' . $response->error->info);
        }

        $requests = $response->query->authmanagerinfo->requests;

        // We don't want to deal with these providers ever.
        $discardList = array(
            // Requires a username and password
            'MediaWiki\\Auth\\PasswordAuthenticationRequest',
        );

        // We require these providers to function
        $requireList = array(
            'MediaWiki\\Auth\\TemporaryPasswordAuthenticationRequest',
            'MediaWiki\\Auth\\UsernameAuthenticationRequest',
            'MediaWiki\\Auth\\UserDataAuthenticationRequest',
            'MediaWiki\\Auth\\CreationReasonAuthenticationRequest',
        );

        $requiredFields = array();
        // Keep checkbox fields separate, since "required" actually means optional as absent == false.
        $czechboxFields = array();

        foreach ($requests as $req) {
            // Immediately discard anything that is on the discard list.
            if (in_array($req->id, $discardList)) {
                continue;
            }

            $required = false;

            if ($req->required === 'primary-required' && !in_array($req->id, $requireList)) {
                // Only want one.
                continue;
            }

            if (in_array($req->id, $requireList)) {
                unset($requireList[$req->id]);
                $required = true;
            }

            if ($req->required === 'required') {
                $required = true;
            }

            if ($required) {
                foreach ($req->fields as $name => $data) {
                    if ($data->type === 'checkbox') {
                        $czechboxFields[] = $name;
                    }
                    else {
                        $requiredFields[] = $name;
                    }
                }
            }
        }
    }
}
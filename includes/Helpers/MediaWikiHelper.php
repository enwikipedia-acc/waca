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
use Waca\Helpers\Interfaces\IMediaWikiClient;
use Waca\SiteConfiguration;

class MediaWikiHelper
{
    /**
     * @var IMediaWikiClient
     */
    private $mediaWikiClient;
    /**
     * @var SiteConfiguration
     */
    private $siteConfiguration;

    /**
     * MediaWikiHelper constructor.
     *
     * @param IMediaWikiClient  $mediaWikiClient
     * @param SiteConfiguration $siteConfiguration
     */
    public function __construct(IMediaWikiClient $mediaWikiClient, SiteConfiguration $siteConfiguration)
    {
        $this->mediaWikiClient = $mediaWikiClient;
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

        $response = $this->mediaWikiClient->doApiCall($tokenParams, 'POST');

        if (isset($response->error)) {
            throw new MediaWikiApiException($response->error->code . ': ' . $response->error->info);
        }

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

        $createResponse = $this->mediaWikiClient->doApiCall($createParams, 'POST');

        if (isset($createResponse->error)) {
            throw new MediaWikiApiException($response->error->code . ': ' . $response->error->info);
        }

        if (!isset($createResponse->createaccount) || !isset($createResponse->createaccount->status)) {
            throw new MediaWikiApiException('Unknown error creating account');
        }

        if ($createResponse->createaccount->status === 'FAIL') {
            throw new MediaWikiApiException($createResponse->createaccount->message);
        }

        if ($createResponse->createaccount->status === 'PASS') {
            // success!
            return;
        }

        throw new Exception('API result reported status of ' . $createResponse->createaccount->status);
    }

    /**
     * @param string $username
     * @param string $title
     * @param        $summary
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

        $response = $this->mediaWikiClient->doApiCall($tokenParams, 'POST');

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

        $response = $this->mediaWikiClient->doApiCall($editParameters, 'POST');

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

    public function getCreationFieldData(&$requiredFields, &$checkboxFields)
    {
        // get token
        $params = array(
            'action'         => 'query',
            'meta'           => 'authmanagerinfo',
            'amirequestsfor' => 'create',
        );

        $response = $this->mediaWikiClient->doApiCall($params, 'GET');

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
        $checkboxFields = array();

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
                        $checkboxFields[] = $name;
                    }
                    else {
                        $requiredFields[] = $name;
                    }
                }
            }
        }
    }

    /**
     * @param string $username
     * @return bool
     */
    public function checkAccountExists($username)
    {
        $parameters = array(
            'action'  => 'query',
            'list'    => 'users',
            'format'  => 'php',
            'ususers' => $username,
        );

        $apiResult = $this->mediaWikiClient->doApiCall($parameters, 'GET');

        $entry = $apiResult->query->users[0];
        $exists = !isset($entry->missing);

        return $exists;
    }

    /**
     * Gets the HTML for the provided wiki-markup
     *
     * @param string $wikiText
     *
     * @return string
     */
    public function getHtmlForWikiText($wikiText)
    {
        $parameters = array(
            'action'             => 'parse',
            'pst'                => true,
            'contentmodel'       => 'wikitext',
            'disablelimitreport' => true,
            'disabletoc'         => true,
            'disableeditsection' => true,
            'text'               => $wikiText,
        );

        $apiResult = $this->mediaWikiClient->doApiCall($parameters, 'GET');

        return $apiResult->parse->text->{'*'};
    }
}

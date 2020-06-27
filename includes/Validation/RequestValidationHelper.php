<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Validation;

use Exception;
use Waca\DataObjects\Request;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IBanHelper;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IAntiSpoofProvider;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\Providers\TorExitProvider;

/**
 * Performs the validation of an incoming request.
 */
class RequestValidationHelper
{
    /** @var IBanHelper */
    private $banHelper;
    /** @var Request */
    private $request;
    private $emailConfirmation;
    /** @var PdoDatabase */
    private $database;
    /** @var IAntiSpoofProvider */
    private $antiSpoofProvider;
    /** @var IXffTrustProvider */
    private $xffTrustProvider;
    /** @var HttpHelper */
    private $httpHelper;
    /**
     * @var string
     */
    private $mediawikiApiEndpoint;
    private $titleBlacklistEnabled;
    /**
     * @var TorExitProvider
     */
    private $torExitProvider;

    /**
     * Summary of __construct
     *
     * @param IBanHelper         $banHelper
     * @param Request            $request
     * @param string             $emailConfirmation
     * @param PdoDatabase        $database
     * @param IAntiSpoofProvider $antiSpoofProvider
     * @param IXffTrustProvider  $xffTrustProvider
     * @param HttpHelper         $httpHelper
     * @param string             $mediawikiApiEndpoint
     * @param boolean            $titleBlacklistEnabled
     * @param TorExitProvider    $torExitProvider
     */
    public function __construct(
        IBanHelper $banHelper,
        Request $request,
        $emailConfirmation,
        PdoDatabase $database,
        IAntiSpoofProvider $antiSpoofProvider,
        IXffTrustProvider $xffTrustProvider,
        HttpHelper $httpHelper,
        $mediawikiApiEndpoint,
        $titleBlacklistEnabled,
        TorExitProvider $torExitProvider
    ) {
        $this->banHelper = $banHelper;
        $this->request = $request;
        $this->emailConfirmation = $emailConfirmation;
        $this->database = $database;
        $this->antiSpoofProvider = $antiSpoofProvider;
        $this->xffTrustProvider = $xffTrustProvider;
        $this->httpHelper = $httpHelper;
        $this->mediawikiApiEndpoint = $mediawikiApiEndpoint;
        $this->titleBlacklistEnabled = $titleBlacklistEnabled;
        $this->torExitProvider = $torExitProvider;
    }

    /**
     * Summary of validateName
     * @return ValidationError[]
     */
    public function validateName()
    {
        $errorList = array();

        // ERRORS
        // name is empty
        if (trim($this->request->getName()) == "") {
            $errorList[ValidationError::NAME_EMPTY] = new ValidationError(ValidationError::NAME_EMPTY);
        }

        // username already exists
        if ($this->userExists()) {
            $errorList[ValidationError::NAME_EXISTS] = new ValidationError(ValidationError::NAME_EXISTS);
        }

        // username part of SUL account
        if ($this->userSulExists()) {
            // using same error slot as name exists - it's the same sort of error, and we probably only want to show one.
            $errorList[ValidationError::NAME_EXISTS] = new ValidationError(ValidationError::NAME_EXISTS_SUL);
        }

        // username is numbers
        if (preg_match("/^[0-9]+$/", $this->request->getName()) === 1) {
            $errorList[ValidationError::NAME_NUMONLY] = new ValidationError(ValidationError::NAME_NUMONLY);
        }

        // username can't contain #@/<>[]|{}
        if (preg_match("/[" . preg_quote("#@/<>[]|{}", "/") . "]/", $this->request->getName()) === 1) {
            $errorList[ValidationError::NAME_INVALIDCHAR] = new ValidationError(ValidationError::NAME_INVALIDCHAR);
        }

        // existing non-closed request for this name
        if ($this->nameRequestExists()) {
            $errorList[ValidationError::OPEN_REQUEST_NAME] = new ValidationError(ValidationError::OPEN_REQUEST_NAME);
        }

        return $errorList;
    }

    /**
     * Summary of validateEmail
     * @return ValidationError[]
     */
    public function validateEmail()
    {
        $errorList = array();

        // ERRORS

        // email addresses must match
        if ($this->request->getEmail() != $this->emailConfirmation) {
            $errorList[ValidationError::EMAIL_MISMATCH] = new ValidationError(ValidationError::EMAIL_MISMATCH);
        }

        // email address must be validly formed
        if (trim($this->request->getEmail()) == "") {
            $errorList[ValidationError::EMAIL_EMPTY] = new ValidationError(ValidationError::EMAIL_EMPTY);
        }

        // email address must be validly formed
        if (!filter_var($this->request->getEmail(), FILTER_VALIDATE_EMAIL)) {
            if (trim($this->request->getEmail()) != "") {
                $errorList[ValidationError::EMAIL_INVALID] = new ValidationError(ValidationError::EMAIL_INVALID);
            }
        }

        // email address can't be wikimedia/wikipedia .com/org
        if (preg_match('/.*@.*wiki(m.dia|p.dia)\.(org|com)/i', $this->request->getEmail()) === 1) {
            $errorList[ValidationError::EMAIL_WIKIMEDIA] = new ValidationError(ValidationError::EMAIL_WIKIMEDIA);
        }

        // WARNINGS

        return $errorList;
    }

    /**
     * Summary of validateOther
     * @return ValidationError[]
     */
    public function validateOther()
    {
        $errorList = array();

        $trustedIp = $this->xffTrustProvider->getTrustedClientIp($this->request->getIp(),
            $this->request->getForwardedIp());

        // ERRORS

        // TOR nodes
        if ($this->torExitProvider->isTorExit($trustedIp)) {
            $errorList[ValidationError::BANNED] = new ValidationError(ValidationError::BANNED_TOR);
        }

        // Bans
        if ($this->banHelper->isBanned($this->request)) {
            $errorList[ValidationError::BANNED] = new ValidationError(ValidationError::BANNED);
        }

        // WARNINGS

        // Antispoof check
        $this->checkAntiSpoof();

        // Blacklist check
        $this->checkTitleBlacklist();

        return $errorList;
    }

    private function checkAntiSpoof()
    {
        try {
            if (count($this->antiSpoofProvider->getSpoofs($this->request->getName())) > 0) {
                // If there were spoofs an Admin should handle the request.
                $this->request->setStatus("Flagged users");
            }
        }
        catch (Exception $ex) {
            // logme
        }
    }

    private function checkTitleBlacklist()
    {
        if ($this->titleBlacklistEnabled == 1) {
            $apiResult = $this->httpHelper->get(
                $this->mediawikiApiEndpoint,
                array(
                    'action'       => 'titleblacklist',
                    'tbtitle'      => $this->request->getName(),
                    'tbaction'     => 'new-account',
                    'tbnooverride' => true,
                    'format'       => 'php',
                )
            );

            $data = unserialize($apiResult);

            $requestIsOk = $data['titleblacklist']['result'] == "ok";

            if (!$requestIsOk) {
                $this->request->setStatus("Flagged users");
            }
        }
    }

    private function userExists()
    {
        $userExists = $this->httpHelper->get(
            $this->mediawikiApiEndpoint,
            array(
                'action'  => 'query',
                'list'    => 'users',
                'ususers' => $this->request->getName(),
                'format'  => 'php',
            )
        );

        $ue = unserialize($userExists);
        if (!isset ($ue['query']['users']['0']['missing']) && isset ($ue['query']['users']['0']['userid'])) {
            return true;
        }

        return false;
    }

    private function userSulExists()
    {
        $requestName = $this->request->getName();

        $userExists = $this->httpHelper->get(
            $this->mediawikiApiEndpoint,
            array(
                'action'  => 'query',
                'meta'    => 'globaluserinfo',
                'guiuser' => $requestName,
                'format'  => 'php',
            )
        );

        $ue = unserialize($userExists);
        if (isset ($ue['query']['globaluserinfo']['id'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a request with this name is currently open
     *
     * @return bool
     */
    private function nameRequestExists()
    {
        $query = "SELECT COUNT(id) FROM request WHERE status != 'Closed' AND name = :name;";
        $statement = $this->database->prepare($query);
        $statement->execute(array(':name' => $this->request->getName()));

        if (!$statement) {
            return false;
        }

        return $statement->fetchColumn() > 0;
    }
}

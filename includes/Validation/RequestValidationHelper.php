<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Validation;

use Exception;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Comment;
use Waca\DataObjects\Request;
use Waca\ExceptionHandler;
use Waca\Exceptions\CurlException;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IBanHelper;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IAntiSpoofProvider;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\Providers\TorExitProvider;
use Waca\RequestStatus;
use Waca\SiteConfiguration;

/**
 * Performs the validation of an incoming request.
 */
class RequestValidationHelper
{
    /** @var IBanHelper */
    private $banHelper;
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
     * @var SiteConfiguration
     */
    private $siteConfiguration;

    private $validationRemoteTimeout = 5000;

    /**
     * Summary of __construct
     *
     * @param IBanHelper         $banHelper
     * @param PdoDatabase        $database
     * @param IAntiSpoofProvider $antiSpoofProvider
     * @param IXffTrustProvider  $xffTrustProvider
     * @param HttpHelper         $httpHelper
     * @param TorExitProvider    $torExitProvider
     * @param SiteConfiguration  $siteConfiguration
     */
    public function __construct(
        IBanHelper $banHelper,
        PdoDatabase $database,
        IAntiSpoofProvider $antiSpoofProvider,
        IXffTrustProvider $xffTrustProvider,
        HttpHelper $httpHelper,
        TorExitProvider $torExitProvider,
        SiteConfiguration $siteConfiguration
    ) {
        $this->banHelper = $banHelper;
        $this->database = $database;
        $this->antiSpoofProvider = $antiSpoofProvider;
        $this->xffTrustProvider = $xffTrustProvider;
        $this->httpHelper = $httpHelper;
        $this->mediawikiApiEndpoint = $siteConfiguration->getMediawikiWebServiceEndpoint();
        $this->titleBlacklistEnabled = $siteConfiguration->getTitleBlacklistEnabled();
        $this->torExitProvider = $torExitProvider;
        $this->siteConfiguration = $siteConfiguration;
    }

    /**
     * Summary of validateName
     *
     * @param Request $request
     *
     * @return ValidationError[]
     */
    public function validateName(Request $request)
    {
        $errorList = array();

        // ERRORS
        // name is empty
        if (trim($request->getName()) == "") {
            $errorList[ValidationError::NAME_EMPTY] = new ValidationError(ValidationError::NAME_EMPTY);
        }

        // username already exists
        if ($this->userExists($request)) {
            $errorList[ValidationError::NAME_EXISTS] = new ValidationError(ValidationError::NAME_EXISTS);
        }

        // username part of SUL account
        if ($this->userSulExists($request)) {
            // using same error slot as name exists - it's the same sort of error, and we probably only want to show one.
            $errorList[ValidationError::NAME_EXISTS] = new ValidationError(ValidationError::NAME_EXISTS_SUL);
        }

        // username is numbers
        if (preg_match("/^[0-9]+$/", $request->getName()) === 1) {
            $errorList[ValidationError::NAME_NUMONLY] = new ValidationError(ValidationError::NAME_NUMONLY);
        }

        // username can't contain #@/<>[]|{}
        if (preg_match("/[" . preg_quote("#@/<>[]|{}", "/") . "]/", $request->getName()) === 1) {
            $errorList[ValidationError::NAME_INVALIDCHAR] = new ValidationError(ValidationError::NAME_INVALIDCHAR);
        }
        
        // username is an IP
        if (filter_var($request->getName(), FILTER_VALIDATE_IP)) {
            $errorList[ValidationError::NAME_IP] = new ValidationError(ValidationError::NAME_IP);
        }

        // existing non-closed request for this name
        if ($this->nameRequestExists($request)) {
            $errorList[ValidationError::OPEN_REQUEST_NAME] = new ValidationError(ValidationError::OPEN_REQUEST_NAME);
        }

        return $errorList;
    }

    /**
     * Summary of validateEmail
     *
     * @param Request $request
     * @param string  $emailConfirmation
     *
     * @return ValidationError[]
     */
    public function validateEmail(Request $request, $emailConfirmation)
    {
        $errorList = array();

        // ERRORS

        // email addresses must match
        if ($request->getEmail() != $emailConfirmation) {
            $errorList[ValidationError::EMAIL_MISMATCH] = new ValidationError(ValidationError::EMAIL_MISMATCH);
        }

        // email address must be validly formed
        if (trim($request->getEmail()) == "") {
            $errorList[ValidationError::EMAIL_EMPTY] = new ValidationError(ValidationError::EMAIL_EMPTY);
        }

        // email address must be validly formed
        if (!filter_var($request->getEmail(), FILTER_VALIDATE_EMAIL)) {
            if (trim($request->getEmail()) != "") {
                $errorList[ValidationError::EMAIL_INVALID] = new ValidationError(ValidationError::EMAIL_INVALID);
            }
        }

        // email address can't be wikimedia/wikipedia .com/org
        if (preg_match('/.*@.*wiki(m.dia|p.dia)\.(org|com)/i', $request->getEmail()) === 1) {
            $errorList[ValidationError::EMAIL_WIKIMEDIA] = new ValidationError(ValidationError::EMAIL_WIKIMEDIA);
        }

        return $errorList;
    }

    /**
     * Summary of validateOther
     *
     * @param Request $request
     *
     * @return ValidationError[]
     */
    public function validateOther(Request $request)
    {
        $errorList = array();

        $trustedIp = $this->xffTrustProvider->getTrustedClientIp($request->getIp(),
            $request->getForwardedIp());

        // ERRORS

        // TOR nodes
        if ($this->torExitProvider->isTorExit($trustedIp)) {
            $errorList[ValidationError::BANNED] = new ValidationError(ValidationError::BANNED_TOR);
        }

        // Bans
        if ($this->banHelper->isBlockBanned($request)) {
            $errorList[ValidationError::BANNED] = new ValidationError(ValidationError::BANNED);
        }

        return $errorList;
    }

    public function postSaveValidations(Request $request)
    {
        // Antispoof check
        $this->checkAntiSpoof($request);

        // Blacklist check
        $this->checkTitleBlacklist($request);

        $bans = $this->banHelper->getBans($request);

        foreach ($bans as $ban) {
            if ($ban->getAction() == Ban::ACTION_DROP) {
                $request->setStatus(RequestStatus::CLOSED);
                $request->save();

                Logger::closeRequest($request->getDatabase(), $request, 0, null);

                $comment = new Comment();
                $comment->setDatabase($this->database);
                $comment->setRequest($request->getId());
                $comment->setVisibility('user');
                $comment->setUser(null);

                $comment->setComment('Request dropped automatically due to matching rule.');
                $comment->save();
            }

            if ($ban->getAction() == Ban::ACTION_DEFER) {
                $this->deferRequest($request, $ban->getActionTarget(), 'Request deferred automatically due to matching rule.');
            }
        }
    }

    private function checkAntiSpoof(Request $request)
    {
        try {
            if (count($this->antiSpoofProvider->getSpoofs($request->getName())) > 0) {
                // If there were spoofs an Admin should handle the request.
                $this->deferRequest($request, 'Flagged users',
                    'Request automatically deferred to flagged users due to AntiSpoof hit');
            }
        }
        catch (Exception $ex) {
            ExceptionHandler::logExceptionToDisk($ex, $this->siteConfiguration);
        }
    }

    private function checkTitleBlacklist(Request $request)
    {
        if ($this->titleBlacklistEnabled == 1) {
            try {
                $apiResult = $this->httpHelper->get(
                    $this->mediawikiApiEndpoint,
                    array(
                        'action'       => 'titleblacklist',
                        'tbtitle'      => $request->getName(),
                        'tbaction'     => 'new-account',
                        'tbnooverride' => true,
                        'format'       => 'php',
                    ),
                    [],
                    $this->validationRemoteTimeout
                );

                $data = unserialize($apiResult);

                $requestIsOk = $data['titleblacklist']['result'] == "ok";
            }
            catch (CurlException $ex) {
                ExceptionHandler::logExceptionToDisk($ex, $this->siteConfiguration);

                // Don't kill the request, just assume it's fine. Humans can deal with it later.
                return;
            }

            if (!$requestIsOk) {
                $this->deferRequest($request, 'Flagged users',
                    'Request automatically deferred to flagged users due to title blacklist hit');
            }
        }
    }

    private function userExists(Request $request)
    {
        try {
            $userExists = $this->httpHelper->get(
                $this->mediawikiApiEndpoint,
                array(
                    'action'  => 'query',
                    'list'    => 'users',
                    'ususers' => $request->getName(),
                    'format'  => 'php',
                ),
                [],
                $this->validationRemoteTimeout
            );

            $ue = unserialize($userExists);
            if (!isset ($ue['query']['users']['0']['missing']) && isset ($ue['query']['users']['0']['userid'])) {
                return true;
            }
        }
        catch (CurlException $ex) {
            ExceptionHandler::logExceptionToDisk($ex, $this->siteConfiguration);

            // Don't kill the request, just assume it's fine. Humans can deal with it later.
            return false;
        }

        return false;
    }

    private function userSulExists(Request $request)
    {
        $requestName = $request->getName();

        try {
            $userExists = $this->httpHelper->get(
                $this->mediawikiApiEndpoint,
                array(
                    'action'  => 'query',
                    'meta'    => 'globaluserinfo',
                    'guiuser' => $requestName,
                    'format'  => 'php',
                ),
                [],
                $this->validationRemoteTimeout
            );

            $ue = unserialize($userExists);
            if (isset ($ue['query']['globaluserinfo']['id'])) {
                return true;
            }
        }
        catch (CurlException $ex) {
            ExceptionHandler::logExceptionToDisk($ex, $this->siteConfiguration);

            // Don't kill the request, just assume it's fine. Humans can deal with it later.
            return false;
        }

        return false;
    }

    /**
     * Checks if a request with this name is currently open
     *
     * @param Request $request
     *
     * @return bool
     */
    private function nameRequestExists(Request $request)
    {
        $query = "SELECT COUNT(id) FROM request WHERE status != 'Closed' AND name = :name;";
        $statement = $this->database->prepare($query);
        $statement->execute(array(':name' => $request->getName()));

        if (!$statement) {
            return false;
        }

        return $statement->fetchColumn() > 0;
    }

    private function deferRequest(Request $request, $targetQueue, $deferComment): void
    {
        $request->setStatus($targetQueue);
        $request->save();

        $logTarget = $this->siteConfiguration->getRequestStates()[$targetQueue]['defertolog'];

        Logger::deferRequest($this->database, $request, $logTarget);

        $comment = new Comment();
        $comment->setDatabase($this->database);
        $comment->setRequest($request->getId());
        $comment->setVisibility('user');
        $comment->setUser(null);

        $comment->setComment($deferComment);
        $comment->save();
    }
}

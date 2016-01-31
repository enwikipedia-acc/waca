<?php

/**
 * Performs the validation of an incoming request.
 */
class RequestValidationHelper
{
	private $banHelper;
	private $request;
	private $emailConfirmation;

	/**
	 * Summary of __construct
	 * @param IBanHelper $banHelper
	 * @param Request $request
	 * @param string $emailConfirmation
	 */
	public function __construct(IBanHelper $banHelper, Request $request, $emailConfirmation)
	{
		$this->banHelper = $banHelper;
		$this->request = $request;
		$this->emailConfirmation = $emailConfirmation;
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

		// name is banned
		$ban = $this->banHelper->nameIsBanned($this->request->getName());
		if ($ban != false) {
			$errorList[ValidationError::BANNED] = new ValidationError(ValidationError::BANNED);
		}

		// username already exists
		// TODO: implement
		if ($this->userExists()) {
			$errorList[ValidationError::NAME_EXISTS] = new ValidationError(ValidationError::NAME_EXISTS);
		}

		// username part of SUL account
		// TODO: implement
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
		// TODO: implement
		if ($this->nameRequestExists()) {
			$errorList[ValidationError::OPEN_REQUEST_NAME] = new ValidationError(ValidationError::OPEN_REQUEST_NAME);
		}

		// WARNINGS
		// name has to be sanitised
		// TODO: implement
		if (false) {
			$errorList[ValidationError::NAME_SANITISED] = new ValidationError(ValidationError::NAME_SANITISED, false);
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

		// Email is banned
		$ban = $this->banHelper->emailIsBanned($this->request->getEmail());
		if ($ban != false) {
			$errorList[ValidationError::BANNED] = new ValidationError(ValidationError::BANNED);
		}

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

		// ERRORS

		// TOR nodes
		// TODO: Implement
		if (false) {
			$errorList[ValidationError::BANNED] = new ValidationError(ValidationError::BANNED_TOR);
		}

		// IP banned
		$ban = $this->banHelper->ipIsBanned($this->request->getTrustedIp());
		if ($ban != false) {
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
		global $antispoofProvider;
		try {
			if (count($antispoofProvider->getSpoofs($this->request->getName())) > 0) {
				// If there were spoofs an Admin should handle the request.
				$this->request->setStatus("Flagged users");
			}
		}
		catch (Exception $ex) {
			// hrm.
			// TODO: log this?
		}
	}

	private function checkTitleBlacklist()
	{
		global $enableTitleblacklist;
		if ($enableTitleblacklist == 1) {
			$apiResult = file_get_contents("https://en.wikipedia.org/w/api.php?action=titleblacklist&tbtitle=" . urlencode($this->request->getName()) . "&tbaction=new-account&tbnooverride&format=php");

			$data = unserialize($apiResult);

			$requestIsOk = $data['titleblacklist']['result'] == "ok";

			if (!$requestIsOk) {
				$this->request->setStatus("Flagged users");
			}
		}
	}

	private function userExists()
	{
		global $mediawikiWebServiceEndpoint;

		$userexist = file_get_contents($mediawikiWebServiceEndpoint . "?action=query&list=users&ususers=" . urlencode($this->request->getName()) . "&format=php");
		$ue = unserialize($userexist);
		if (!isset ($ue['query']['users']['0']['missing']) && isset ($ue['query']['users']['0']['userid'])) {
			return true;
		}

		return false;
	}

	private function userSulExists()
	{
		global $mediawikiWebServiceEndpoint;

		$reqname = str_replace("_", " ", $this->request->getName());
		$userexist = file_get_contents($mediawikiWebServiceEndpoint . "?action=query&meta=globaluserinfo&guiuser=" . urlencode($reqname) . "&format=php");
		$ue = unserialize($userexist);
		if (isset ($ue['query']['globaluserinfo']['id'])) {
			return true;
		}

		return false;
	}

	private function nameRequestExists()
	{
		$query = "SELECT COUNT(id) FROM request WHERE status != 'Closed' AND name = :name;";
		$statement = gGetDb()->prepare($query);
		$statement->execute(array(':name' => $this->request->getName()));

		if (!$statement) {
			return false;
		}

		return $statement->fetchColumn() > 0;
	}
}

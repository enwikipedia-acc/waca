<?php

namespace Waca\Security;

use Exception;
use Waca\WebRequest;

class TokenManager
{
	/**
	 * @param string $context
	 * @param string $data
	 *
	 * @return bool
	 */
	public function validateToken($context, $data)
	{
		if (!is_string($data) || strlen($data) === 0) {
			// Nothing to validate
			return false;
		}

		$tokens = WebRequest::getSessionTokenData();

		// if the token doesn't exist, then it's not valid
		if (!array_key_exists($data, $tokens)) {
			return false;
		}

		/** @var Token $token */
		$token = unserialize($tokens[$data]);

		if ($token->getTokenData() !== $data) {
			return false;
		}

		if ($token->getContext() !== $context) {
			return false;
		}

		if ($token->isUsed()) {
			return false;
		}

		// mark the token as used, and save it back to the session
		$token->markAsUsed();
		$this->storeToken($token);

		return true;
	}

	/**
	 * @param string $context
	 *
	 * @return Token
	 */
	public function getNewToken($context)
	{
		// search for an existing token with this context path
		$retrievedToken = null;
		$tokens = WebRequest::getSessionTokenData();
		foreach ($tokens as $tokenData => $serialisedToken) {
			/** @var Token $token */
			$token = unserialize($serialisedToken);

			if (!$token->isUsed() && $token->getContext() === $context) {
				$retrievedToken = $token;
				break;
			}
		}

		if ($retrievedToken instanceof Token) {
			$token = $retrievedToken;
		}
		else {
			$token = new Token($this->generateTokenData(), $context);
			$this->storeToken($token);
		}

		return $token;
	}

	/**
	 * Stores a token in the session data
	 *
	 * @param Token $token
	 */
	private function storeToken(Token $token)
	{
		$tokens = WebRequest::getSessionTokenData();
		$tokens[$token->getTokenData()] = serialize($token);
		WebRequest::setSessionTokenData($tokens);
	}

	/**
	 * Generates a security token
	 *
	 * @return string
	 * @throws Exception
	 *
	 * @category Security-Critical
	 */
	private function generateTokenData()
	{
		$genBytes = openssl_random_pseudo_bytes(33);

		if ($genBytes !== false) {
			return base64_encode($genBytes);
		}

		throw new Exception('Unable to generate secure token.');
	}
}
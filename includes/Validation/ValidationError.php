<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Validation;

use Exception;

class ValidationError
{
    const NAME_EMPTY = "name_empty";
    const NAME_EXISTS = "name_exists";
    const NAME_EXISTS_SUL = "name_exists_sul";
    const NAME_NUMONLY = "name_numonly";
    const NAME_INVALIDCHAR = "name_invalidchar";
    const NAME_SANITISED = "name_sanitised";
    const NAME_IP = "name_ip";
    const EMAIL_EMPTY = "email_empty";
    const EMAIL_WIKIMEDIA = "email_wikimedia";
    const EMAIL_INVALID = "email_invalid";
    const EMAIL_MISMATCH = "email_mismatch";
    const OPEN_REQUEST_NAME = "open_request_name";
    const BANNED = "banned";
    const BANNED_TOR = "banned_tor";
    /**
     * @var array Error text for the above
     */
    private static $errorText = array(
        self::NAME_EMPTY        => 'You\'ve not chosen a username!',
        self::NAME_EXISTS       => 'I\'m sorry, but the username you selected is already taken. Please try another. '
            . 'Please note that Wikipedia automatically capitalizes the first letter of any user name, therefore '
            . '[[User:example]] would become [[User:Example]].',
        self::NAME_EXISTS_SUL   => 'I\'m sorry, but the username you selected is already taken. Please try another. '
            . 'Please note that Wikipedia automatically capitalizes the first letter of any user name, therefore '
            . '[[User:example]] would become [[User:Example]].',
        self::NAME_NUMONLY      => 'The username you chose is invalid: it consists entirely of numbers. Please retry '
            . 'with a valid username.',
        self::NAME_INVALIDCHAR  => 'There appears to be an invalid character in your username. Please note that the '
            . 'following characters are not allowed: <code># @ / &lt; &gt; [ ] | { }</code>',
        self::NAME_SANITISED    => 'Your requested username has been automatically adjusted due to technical '
            . 'restrictions. Underscores have been replaced with spaces, and the first character has been capitalised.',
        self::NAME_IP           => 'The username you chose is invalid: it cannot be an IP address',
        self::EMAIL_EMPTY       => 'You need to supply an email address.',
        self::EMAIL_WIKIMEDIA   => 'Please provide your email address here.',
        self::EMAIL_INVALID     => 'Invalid E-mail address supplied. Please check you entered it correctly.',
        self::EMAIL_MISMATCH    => 'The email addresses you entered do not match. Please try again.',
        self::OPEN_REQUEST_NAME => 'There is already an open request with this name in this system.',
        self::BANNED            => 'Sorry, you are currently banned from requesting accounts using this tool.',
        self::BANNED_TOR        => 'Tor exit nodes are currently banned from using this tool due to excessive abuse. '
            . 'Please note that Tor is also currently banned from editing Wikipedia.',
    );
    /**
     * Summary of $errorCode
     * @var string
     */
    private $errorCode;
    /**
     * Summary of $isError
     * @var bool
     */
    private $isError;

    /**
     * Summary of __construct
     *
     * @param string $errorCode
     * @param bool   $isError
     */
    public function __construct($errorCode, $isError = true)
    {
        $this->errorCode = $errorCode;
        $this->isError = $isError;
    }

    /**
     * Summary of getErrorCode
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getErrorMessage()
    {
        $text = self::$errorText[$this->errorCode];

        if ($text == null) {
            throw new Exception('Unknown validation error');
        }

        return $text;
    }

    /**
     * Summary of isError
     * @return bool
     */
    public function isError()
    {
        return $this->isError;
    }
}

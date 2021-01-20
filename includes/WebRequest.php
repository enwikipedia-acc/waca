<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/** @noinspection PhpPassByRefInspection - disable seemingly broken check in PhpStorm */

namespace Waca;

use Waca\DataObjects\User;
use Waca\Providers\GlobalState\IGlobalStateProvider;
use Webauthn\PublicKeyCredentialCreationOptions;

/**
 * Holds helper functions regarding the current request.
 *
 * This is the only place where it is allowed to use super-globals, but even then access MUST be pushed through the
 * global state provider to allow for unit tests. It's strongly recommended to do sanitising of data here, especially
 * if extra logic is required to get a deterministic value, like isHttps().
 *
 * @package Waca
 */
class WebRequest
{
    /**
     * @var IGlobalStateProvider Provides access to the global state.
     */
    private static $globalStateProvider;

    /**
     * Returns a boolean value if the request was submitted with the HTTP POST method.
     * @return bool
     */
    public static function wasPosted()
    {
        return self::method() === 'POST';
    }

    /**
     * Gets the HTTP Method used
     * @return string|null
     */
    public static function method()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['REQUEST_METHOD'])) {
            return $server['REQUEST_METHOD'];
        }

        return null;
    }

    /**
     * Gets a boolean value stating whether the request was served over HTTPS or not.
     * @return bool
     */
    public static function isHttps()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['HTTP_X_FORWARDED_PROTO'])) {
            if ($server['HTTP_X_FORWARDED_PROTO'] === 'https') {
                // Client <=> Proxy is encrypted
                return true;
            }
            else {
                // Proxy <=> Server link unknown, Client <=> Proxy is not encrypted.
                return false;
            }
        }

        if (isset($server['HTTPS'])) {
            if ($server['HTTPS'] === 'off') {
                // ISAPI on IIS breaks the spec. :(
                return false;
            }

            if ($server['HTTPS'] !== '') {
                // Set to a non-empty value
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the path info
     *
     * @return array Array of path info segments
     */
    public static function pathInfo()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();
        if (!isset($server['PATH_INFO'])) {
            return array();
        }

        $exploded = explode('/', $server['PATH_INFO']);

        // filter out empty values, and reindex from zero. Notably, the first element is always zero, since it starts
        // with a /
        return array_values(array_filter($exploded));
    }

    /**
     * Gets the remote address of the web request
     * @return null|string
     */
    public static function remoteAddress()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['REMOTE_ADDR'])) {
            return $server['REMOTE_ADDR'];
        }

        return null;
    }

    /**
     * Gets the remote address of the web request
     * @return null|string
     */
    public static function httpHost()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['HTTP_HOST'])) {
            return $server['HTTP_HOST'];
        }

        return null;
    }

    /**
     * Gets the XFF header contents for the web request
     * @return null|string
     */
    public static function forwardedAddress()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['HTTP_X_FORWARDED_FOR'])) {
            return $server['HTTP_X_FORWARDED_FOR'];
        }

        return null;
    }

    /**
     * Sets the global state provider.
     *
     * Almost guaranteed this is not the method you want in production code.
     *
     * @param IGlobalStateProvider $globalState
     */
    public static function setGlobalStateProvider($globalState)
    {
        self::$globalStateProvider = $globalState;
    }

    #region POST variables

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function postString($key)
    {
        $post = &self::$globalStateProvider->getPostSuperGlobal();
        if (!array_key_exists($key, $post)) {
            return null;
        }

        if ($post[$key] === "") {
            return null;
        }

        return (string)$post[$key];
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function postEmail($key)
    {
        $post = &self::$globalStateProvider->getPostSuperGlobal();
        if (!array_key_exists($key, $post)) {
            return null;
        }

        $filteredValue = filter_var($post[$key], FILTER_SANITIZE_EMAIL);

        if ($filteredValue === false) {
            return null;
        }

        return (string)$filteredValue;
    }

    /**
     * @param string $key
     *
     * @return int|null
     */
    public static function postInt($key)
    {
        $post = &self::$globalStateProvider->getPostSuperGlobal();
        if (!array_key_exists($key, $post)) {
            return null;
        }

        $filteredValue = filter_var($post[$key], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        if ($filteredValue === null) {
            return null;
        }

        return (int)$filteredValue;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function postBoolean($key)
    {
        $get = &self::$globalStateProvider->getPostSuperGlobal();
        if (!array_key_exists($key, $get)) {
            return false;
        }

        // presence of parameter only
        if ($get[$key] === "") {
            return true;
        }

        if (in_array($get[$key], array(false, 'no', 'off', 0, 'false'), true)) {
            return false;
        }

        return true;
    }

    #endregion

    #region GET variables

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function getBoolean($key)
    {
        $get = &self::$globalStateProvider->getGetSuperGlobal();
        if (!array_key_exists($key, $get)) {
            return false;
        }

        // presence of parameter only
        if ($get[$key] === "") {
            return true;
        }

        if (in_array($get[$key], array(false, 'no', 'off', 0, 'false'), true)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @return int|null
     */
    public static function getInt($key)
    {
        $get = &self::$globalStateProvider->getGetSuperGlobal();
        if (!array_key_exists($key, $get)) {
            return null;
        }

        $filteredValue = filter_var($get[$key], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        if ($filteredValue === null) {
            return null;
        }

        return (int)$filteredValue;
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function getString($key)
    {
        $get = &self::$globalStateProvider->getGetSuperGlobal();
        if (!array_key_exists($key, $get)) {
            return null;
        }

        if ($get[$key] === "") {
            return null;
        }

        return (string)$get[$key];
    }

    #endregion

    /**
     * Sets the logged-in user to the specified user.
     *
     * @param User $user
     */
    public static function setLoggedInUser(User $user)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();

        $session['userID'] = $user->getId();
        unset($session['partialLogin']);
    }

    /**
     * Sets the post-login redirect
     *
     * @param string|null $uri The URI to redirect to
     */
    public static function setPostLoginRedirect($uri = null)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();

        if ($uri === null) {
            $uri = self::requestUri();
        }

        $session['returnTo'] = $uri;
    }

    /**
     * @return string|null
     */
    public static function requestUri()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['REQUEST_URI'])) {
            return $server['REQUEST_URI'];
        }

        return null;
    }

    /**
     * Clears the post-login redirect
     * @return string
     */
    public static function clearPostLoginRedirect()
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        if (array_key_exists('returnTo', $session)) {
            $path = $session['returnTo'];
            unset($session['returnTo']);

            return $path;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public static function serverName()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['SERVER_NAME'])) {
            return $server['SERVER_NAME'];
        }

        return null;
    }

    /**
     * You probably only want to deal with this through SessionAlert.
     * @return void
     */
    public static function clearSessionAlertData()
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        if (array_key_exists('alerts', $session)) {
            unset($session['alerts']);
        }
    }

    /**
     * You probably only want to deal with this through SessionAlert.
     *
     * @return string[]
     */
    public static function getSessionAlertData()
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        if (array_key_exists('alerts', $session)) {
            return $session['alerts'];
        }

        return array();
    }

    /**
     * You probably only want to deal with this through SessionAlert.
     *
     * @param string[] $data
     */
    public static function setSessionAlertData($data)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        $session['alerts'] = $data;
    }

    /**
     * You probably only want to deal with this through TokenManager.
     *
     * @return string[]
     */
    public static function getSessionTokenData()
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        if (array_key_exists('tokens', $session)) {
            return $session['tokens'];
        }

        return array();
    }

    /**
     * You probably only want to deal with this through TokenManager.
     *
     * @param string[] $data
     */
    public static function setSessionTokenData($data)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        $session['tokens'] = $data;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function getSessionContext($key)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();

        if (!isset($session['context'])) {
            $session['context'] = array();
        }

        if (!isset($session['context'][$key])) {
            return null;
        }

        return $session['context'][$key];
    }

    /**
     * @param string $key
     * @param mixed  $data
     */
    public static function setSessionContext($key, $data)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();

        if (!isset($session['context'])) {
            $session['context'] = array();
        }

        $session['context'][$key] = $data;
    }

    /**
     * @return int|null
     */
    public static function getSessionUserId()
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();

        return isset($session['userID']) ? (int)$session['userID'] : null;
    }

    /**
     * @param User $user
     */
    public static function setOAuthPartialLogin(User $user)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        $session['oauthPartialLogin'] = $user->getId();
    }

    /**
     * @return int|null
     */
    public static function getOAuthPartialLogin()
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();

        return isset($session['oauthPartialLogin']) ? (int)$session['oauthPartialLogin'] : null;
    }

    public static function setAuthPartialLogin($userId, $stage)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        $session['authPartialLoginId'] = $userId;
        $session['authPartialLoginStage'] = $stage;
    }

    public static function setAuthPartialLoginToken($token)
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        $session['authPartialLoginToken'] = $token;
    }

    public static function getAuthPartialLogin()
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();

        $userId = isset($session['authPartialLoginId']) ? (int)$session['authPartialLoginId'] : null;
        $stage = isset($session['authPartialLoginStage']) ? (int)$session['authPartialLoginStage'] : null;
        $token = isset($session['authPartialLoginToken']) ? $session['authPartialLoginToken'] : null;

        return array($userId, $stage, $token);
    }

    public static function clearAuthPartialLogin()
    {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        unset($session['authPartialLoginId']);
        unset($session['authPartialLoginStage']);
        unset($session['authPartialLoginToken']);
    }

    public static function setWebAuthnOptions($options) {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        $session['webAuthnPubkeyOptions'] = serialize($options);
    }

    public static function getWebAuthnOptions() {
        $session = &self::$globalStateProvider->getSessionSuperGlobal();
        $decodedObject = unserialize($session['webAuthnPubkeyOptions']);

        return $decodedObject;
    }

    /**
     * @return null|string
     */
    public static function userAgent()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['HTTP_USER_AGENT'])) {
            return $server['HTTP_USER_AGENT'];
        }

        return null;
    }

    /**
     * @return null|string
     */
    public static function scriptName()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['SCRIPT_NAME'])) {
            return $server['SCRIPT_NAME'];
        }

        return null;
    }

    /**
     * @return null|string
     */
    public static function origin()
    {
        $server = &self::$globalStateProvider->getServerSuperGlobal();

        if (isset($server['HTTP_ORIGIN'])) {
            return $server['HTTP_ORIGIN'];
        }

        return null;
    }

    public static function testSiteNoticeCookieValue($expectedHash)
    {
        $cookie = &self::$globalStateProvider->getCookieSuperGlobal();

        if(isset($cookie['sitenotice'])) {
            return $cookie['sitenotice'] === $expectedHash;
        }

        return false;
    }
}

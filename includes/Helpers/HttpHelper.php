<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\Exceptions\CurlException;

class HttpHelper
{
    private $curlHandle;

    /**
     * HttpHelper constructor.
     *
     * @param string  $userAgent
     * @param boolean $disableVerifyPeer
     */
    public function __construct($userAgent, $disableVerifyPeer)
    {
        $this->curlHandle = curl_init();

        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($this->curlHandle, CURLOPT_FAILONERROR, true);

        if ($disableVerifyPeer) {
            curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        }
    }

    public function __destruct()
    {
        curl_close($this->curlHandle);
    }

    /**
     * Fetches the content of a URL, with an optional parameter set.
     *
     * @param string     $url        The URL to fetch.
     * @param null|array $parameters Key/value pair of GET parameters to add to the request.
     *                               Null lets you handle it yourself.
     *
     * @param array      $headers
     *
     * @return string
     * @throws CurlException
     */
    public function get($url, $parameters = null, $headers = array())
    {
        if ($parameters !== null && is_array($parameters)) {
            $getString = '?' . http_build_query($parameters);
            $url .= $getString;
        }

        curl_setopt($this->curlHandle, CURLOPT_URL, $url);

        // Make sure we're doing a GET
        curl_setopt($this->curlHandle, CURLOPT_POST, false);

        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($this->curlHandle);

        if ($result === false) {
            $error = curl_error($this->curlHandle);
            throw new CurlException('Remote request failed with error ' . $error);
        }

        return $result;
    }

    /**
     * Posts data to a URL
     *
     * @param string $url        The URL to fetch.
     * @param array  $parameters Key/value pair of POST parameters to add to the request.
     * @param array  $headers
     *
     * @return string
     * @throws CurlException
     */
    public function post($url, $parameters, $headers = array())
    {
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);

        // Make sure we're doing a POST
        curl_setopt($this->curlHandle, CURLOPT_POST, true);
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, http_build_query($parameters));

        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($this->curlHandle);

        if ($result === false) {
            $error = curl_error($this->curlHandle);
            throw new CurlException('Remote request failed with error ' . $error);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return curl_error($this->curlHandle);
    }
}
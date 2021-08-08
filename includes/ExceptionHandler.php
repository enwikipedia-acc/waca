<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use ErrorException;
use Throwable;

class ExceptionHandler
{
    /**
     * Global exception handler
     *
     * Smarty would be nice to use, but it COULD BE smarty that throws the errors.
     * Let's build something ourselves, and hope it works.
     *
     * @param $exception
     *
     * @category Security-Critical - has the potential to leak data when exception is thrown.
     */
    public static function exceptionHandler(Throwable $exception)
    {
        /** @global $siteConfiguration SiteConfiguration */
        global $siteConfiguration;

        $errorDocument = <<<HTML
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8">
<title>Oops! Something went wrong!</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$siteConfiguration->getBaseUrl()}/resources/generated/bootstrap-main.css" rel="stylesheet">
<style>
  body {
    padding-top: 60px;
  }
</style>
</head><body><div class="container">
<h1>Oops! Something went wrong!</h1>
<p>We'll work on fixing this for you, so why not come back later?</p>
<p class="muted">If our trained monkeys ask, tell them this error ID: <code>$1$</code></p>
$2$
</div></body></html>
HTML;

        list($errorData, $errorId) = self::logExceptionToDisk($exception, $siteConfiguration, true);

        // clear and discard any content that's been saved to the output buffer
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // push error ID into the document.
        $message = str_replace('$1$', $errorId, $errorDocument);

        if ($siteConfiguration->getDebuggingTraceEnabled()) {
            ob_start();
            var_dump($errorData);
            $textErrorData = ob_get_contents();
            ob_end_clean();

            $message = str_replace('$2$', $textErrorData, $message);
        }
        else {
            $message = str_replace('$2$', "", $message);
        }

        // While we *shouldn't* have sent headers by now due to the output buffering, PHPUnit does weird things.
        // This is "only" needed for the tests, but it's a good idea to wrap this anyway.
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        // output the document
        print $message;
    }

    /**
     * @param int    $errorSeverity The severity level of the exception.
     * @param string $errorMessage  The Exception message to throw.
     * @param string $errorFile     The filename where the exception is thrown.
     * @param int    $errorLine     The line number where the exception is thrown.
     *
     * @throws ErrorException
     */
    public static function errorHandler($errorSeverity, $errorMessage, $errorFile, $errorLine)
    {
        // call into the main exception handler above
        throw new ErrorException($errorMessage, 0, $errorSeverity, $errorFile, $errorLine);
    }

    /**
     * @param Throwable $exception
     *
     * @return null|array
     */
    public static function getExceptionData($exception)
    {
        if ($exception == null) {
            return null;
        }

        $array = array(
            'exception' => get_class($exception),
            'message'   => $exception->getMessage(),
            'stack'     => $exception->getTraceAsString(),
        );

        $array['previous'] = self::getExceptionData($exception->getPrevious());

        return $array;
    }

    /**
     * @param Throwable         $exception
     * @param SiteConfiguration $siteConfiguration
     * @param bool              $fromGlobalHandler
     *
     * @return array
     */
    public static function logExceptionToDisk(
        Throwable $exception,
        SiteConfiguration $siteConfiguration,
        bool $fromGlobalHandler = false
    ): array {
        $errorData = self::getExceptionData($exception);
        $errorData['server'] = $_SERVER;
        $errorData['get'] = $_GET;
        $errorData['post'] = $_POST;

        if ($fromGlobalHandler) {
            $errorData['globalHandler'] = true;
        }
        else {
            $errorData['globalHandler'] = false;
        }

        $state = serialize($errorData);
        $errorId = sha1($state);

        // Save the error for later analysis
        file_put_contents($siteConfiguration->getErrorLog() . '/' . $errorId . '.log', $state);

        return array($errorData, $errorId);
    }
}

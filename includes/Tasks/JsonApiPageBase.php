<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tasks;

use Waca\API\ApiException;
use Waca\API\IJsonApiAction;
use Waca\WebRequest;

abstract class JsonApiPageBase extends ApiPageBase implements IJsonApiAction
{
    /**
     * Main function for this page, when no specific actions are called.
     *
     * @return void
     * @throws ApiException
     */
    final protected function main()
    {
        if (headers_sent()) {
            throw new ApiException('Headers have already been sent - this indicates a bug in the application!');
        }

        // javascript access control
        $httpOrigin = WebRequest::origin();

        if ($httpOrigin !== null) {
            $CORSallowed = $this->getSiteConfiguration()->getCrossOriginResourceSharingHosts();

            if (in_array($httpOrigin, $CORSallowed)) {
                header("Access-Control-Allow-Origin: " . $httpOrigin);
            }
        }

        $responseData = $this->runApiPage();

        ob_end_clean();
        print($responseData);
        ob_start();
    }

    /**
     * Method that runs API action
     *
     * @return object|array The modified API document
     */
    public abstract function executeApiAction();

    /**
     * @return string
     */
    final public function runApiPage()
    {
        try {
            $apiDocument = $this->executeApiAction();
        }
            /** @noinspection PhpRedundantCatchClauseInspection */
        catch (ApiException $ex) {
            $apiDocument = [
                'error' => $ex->getMessage(),
            ];
        }

        $data = json_encode($apiDocument, JSON_UNESCAPED_UNICODE);

        $targetVar = WebRequest::getString('targetVariable');
        if ($targetVar !== null && preg_match('/^[a-z]+$/', $targetVar)) {
            $data = $targetVar . ' = ' . $data . ';';
            header("Content-Type: text/javascript");
        }
        else {
            header("Content-Type: application/json");
        }

        return $data;
    }
}

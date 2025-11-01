<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tasks;

use Waca\API\ApiException as ApiException;

abstract class TextApiPageBase extends ApiPageBase implements IRoutedTask
{
    final protected function main()
    {
        if (headers_sent()) {
            throw new ApiException('Headers have already been sent - this indicates a bug in the application!');
        }

        try {
            $responseData = $this->runApiPage();
        }
        catch (ApiException $ex) {
            $responseData = $ex->getMessage();
        }

        header('Content-Type: text/plain');

        ob_end_clean();
        print($responseData);
        ob_start();
    }
}

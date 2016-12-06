<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Waca\DataObjects\Request;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PdoDatabase;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

abstract class RequestActionBase extends InternalPageBase
{
    /**
     * @param PdoDatabase $database
     *
     * @return Request
     * @throws ApplicationLogicException
     */
    protected function getRequest(PdoDatabase $database)
    {
        $requestId = WebRequest::postInt('request');
        if ($requestId === null) {
            throw new ApplicationLogicException('Request ID not found');
        }

        /** @var Request $request */
        $request = Request::getById($requestId, $database);

        if ($request === false) {
            throw new ApplicationLogicException('Request not found');
        }

        return $request;
    }

    final protected function checkPosted()
    {
        // if the request was not posted, send the user away.
        if (!WebRequest::wasPosted()) {
            throw new ApplicationLogicException('This page does not support GET methods.');
        }

        // validate the CSRF token
        $this->validateCSRFToken();
    }
}
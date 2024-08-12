<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Helpers\Logger;
use Waca\WebRequest;

class PageManuallyConfirm extends RequestActionBase
{
    /**
     * This endpoint manually confirms a request, bypassing email confirmation.
     *
     * Only administrators are allowed to do this, for obvious reasons.
     *
     * @throws ApplicationLogicException|OptimisticLockFailedException
     */
    protected function main()
    {
        // This method throws an error if we don't post
        $this->checkPosted();

        // Retrieve the database.
        $database = $this->getDatabase();

        // Find the request
        // This method throws exceptions if there is an error with the request.
        $request = $this->getRequest($database);
        $version = WebRequest::postInt('version');

        $request->setUpdateVersion($version);

        // Mark the request as confirmed.
        $request->setEmailConfirm("Confirmed");
        $request->save();

        // Log that the request was manually confirmed
        Logger::manuallyConfirmRequest($database, $request);

        // Notify the IRC channel
        $this->getNotificationHelper()->requestReceived($request);

        // Redirect back to the main request, now it should show the request.
        $this->redirect('viewRequest', null, array('id' => $request->getId()));
    }
}

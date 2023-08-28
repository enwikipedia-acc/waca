<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\Request;

use Exception;
use Waca\DataObjects\Request;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Helpers\Logger;
use Waca\RequestStatus;
use Waca\Tasks\PublicInterfacePageBase;
use Waca\WebRequest;

class PageConfirmEmail extends PublicInterfacePageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @throws ApplicationLogicException
     * @throws Exception
     */
    protected function main()
    {
        $id = WebRequest::getInt('id');
        $si = WebRequest::getString('si');

        if ($id === null || $si === null) {
            throw new ApplicationLogicException('Link incomplete - please double check the link you received.');
        }

        /** @var Request|false $request */
        $request = Request::getById($id, $this->getDatabase());

        if ($request === false) {
            throw new ApplicationLogicException('Request not found');
        }

        if ($request->getEmailConfirm() === 'Confirmed') {
            // request has already been confirmed. Bomb out silently.
            $this->redirect('requestSubmitted');

            return;
        }

        if ($request->getEmailConfirm() === $si) {
            $request->setEmailConfirm('Confirmed');
        }
        else {
            throw new ApplicationLogicException('The confirmation value does not appear to match the expected value');
        }

        try {
            $request->save();
        }
        catch (OptimisticLockFailedException $ex) {
            // Okay. Someone's edited this in the time between us loading this page and doing the checks, and us getting
            // to saving the page. We *do not* want to show an optimistic lock failure, the most likely problem is they
            // double-loaded this page (see #255). Let's confirm this, and bomb out with a success message if it's the
            // case.

            $request = Request::getById($id, $this->getDatabase());
            if ($request->getEmailConfirm() === 'Confirmed') {
                // we've already done the sanity checks above

                $this->redirect('requestSubmitted');

                // skip the log and notification
                return;
            }

            // something really weird happened. Another race condition?
            throw $ex;
        }

        Logger::emailConfirmed($this->getDatabase(), $request);

        if ($request->getStatus() != RequestStatus::CLOSED) {
            $this->getNotificationHelper()->requestReceived($request);
        }

        $this->redirect('requestSubmitted');
    }
}
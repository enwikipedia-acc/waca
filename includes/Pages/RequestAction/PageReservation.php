<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use DateTime;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\SessionAlert;
use Waca\WebRequest;

class PageReservation extends RequestActionBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @throws ApplicationLogicException
     */
    protected function main()
    {
        $this->checkPosted();
        $database = $this->getDatabase();
        $request = $this->getRequest($database);

        $closureDate = $request->getClosureDate();

        $date = new DateTime();
        $date->modify("-7 days");
        $oneweek = $date->format("Y-m-d H:i:s");

        $currentUser = User::getCurrent($database);
        if ($request->getStatus() == "Closed" && $closureDate < $oneweek) {
            if (!$this->barrierTest('reopenOldRequest', $currentUser, 'RequestData')) {
                throw new ApplicationLogicException(
                    "You are not allowed to reserve a request that has been closed for over a week.");
            }
        }

        if ($request->getReserved() !== null && $request->getReserved() != $currentUser->getId()) {
            throw new ApplicationLogicException("Request is already reserved!");
        }

        if ($request->getReserved() === null) {
            // Check the number of requests a user has reserved already
            $doubleReserveCountQuery = $database->prepare("SELECT COUNT(*) FROM request WHERE reserved = :userid;");
            $doubleReserveCountQuery->bindValue(":userid", $currentUser->getId());
            $doubleReserveCountQuery->execute();
            $doubleReserveCount = $doubleReserveCountQuery->fetchColumn();
            $doubleReserveCountQuery->closeCursor();

            // User already has at least one reserved.
            if ($doubleReserveCount != 0) {
                SessionAlert::warning("You have multiple requests reserved!");
            }

            $request->setReserved($currentUser->getId());
            $request->setUpdateVersion(WebRequest::postInt('updateversion'));
            $request->save();

            Logger::reserve($database, $request);

            $this->getNotificationHelper()->requestReserved($request);

            SessionAlert::success("Reserved request {$request->getId()}.");
        }

        $this->redirect('viewRequest', null, array('id' => $request->getId()));
    }
}

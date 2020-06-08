<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Exception;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\SessionAlert;
use Waca\WebRequest;

class PageSendToUser extends RequestActionBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @throws ApplicationLogicException
     * @throws Exception
     */
    protected function main()
    {
        $this->checkPosted();
        $database = $this->getDatabase();
        $request = $this->getRequest($database);

        if ($request->getReserved() !== User::getCurrent($database)->getId()) {
            throw new ApplicationLogicException('You don\'t have this request reserved!');
        }

        $username = WebRequest::postString('user');
        if ($username === null) {
            throw new ApplicationLogicException('User must be specified');
        }

        $user = User::getByUsername($username, $database);
        if ($user === false) {
            throw new ApplicationLogicException('User not found');
        }

        if (!$user->isActive()) {
            throw new ApplicationLogicException('User is currently not active on the tool');
        }

        $request->setReserved($user->getId());
        $request->setUpdateVersion(WebRequest::postInt('updateversion'));
        $request->save();

        Logger::sendReservation($database, $request, $user);
        $this->getNotificationHelper()->requestReservationSent($request, $user);
        SessionAlert::success("Reservation sent successfully");

        $this->redirect('viewRequest', null, array('id' => $request->getId()));
    }
}

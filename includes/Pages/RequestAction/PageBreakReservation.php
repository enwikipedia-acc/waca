<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\RequestAction;

use Exception;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\WebRequest;

class PageBreakReservation extends RequestActionBase
{
    protected function main()
    {
        $this->checkPosted();
        $database = $this->getDatabase();
        $request = $this->getRequest($database);

        if ($request->getReserved() === null) {
            throw new ApplicationLogicException('Request is not reserved!');
        }

        $currentUser = User::getCurrent($database);

        if ($currentUser->getId() === $request->getReserved()) {
            $this->doUnreserve($request, $database);
        }
        else {
            // not the same user!
            if ($this->barrierTest('force', $currentUser)) {
                $this->doBreakReserve($request, $database);
            }
            else {
                throw new AccessDeniedException($this->getSecurityManager());
            }
        }
    }

    /**
     * @param Request     $request
     * @param PdoDatabase $database
     *
     * @throws Exception
     */
    protected function doUnreserve(Request $request, PdoDatabase $database)
    {
        // same user! we allow people to unreserve their own stuff
        $request->setReserved(null);
        $request->setUpdateVersion(WebRequest::postInt('updateversion'));
        $request->save();

        Logger::unreserve($database, $request);
        $this->getNotificationHelper()->requestUnreserved($request);

        // Redirect home!
        $this->redirect();
    }

    /**
     * @param Request     $request
     * @param PdoDatabase $database
     *
     * @throws Exception
     */
    protected function doBreakReserve(Request $request, PdoDatabase $database)
    {
        if (!WebRequest::postBoolean("confirm")) {
            $this->assignCSRFToken();

            $this->assign("request", $request->getId());
            $this->assign("reservedUser", User::getById($request->getReserved(), $database));
            $this->assign("updateversion", WebRequest::postInt('updateversion'));

            $this->setTemplate("confirmations/breakreserve.tpl");
        }
        else {
            $request->setReserved(null);
            $request->setUpdateVersion(WebRequest::postInt('updateversion'));
            $request->save();

            Logger::breakReserve($database, $request);
            $this->getNotificationHelper()->requestReserveBroken($request);

            // Redirect home!
            $this->redirect();
        }
    }
}

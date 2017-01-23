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

class PageDeferRequest extends RequestActionBase
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
        $currentUser = User::getCurrent($database);

        $target = WebRequest::postString('target');
        $requestStates = $this->getSiteConfiguration()->getRequestStates();

        if (!array_key_exists($target, $requestStates)) {
            throw new ApplicationLogicException('Defer target not valid');
        }

        if ($request->getStatus() == $target) {
            SessionAlert::warning('This request is already in the specified queue.');
            $this->redirect('viewRequest', null, array('id' => $request->getId()));

            return;
        }

        $closureDate = $request->getClosureDate();
        $date = new DateTime();
        $date->modify("-7 days");
        $oneweek = $date->format("Y-m-d H:i:s");


        if ($request->getStatus() == "Closed" && $closureDate < $oneweek) {
            if (!$this->barrierTest('reopenOldRequest', $currentUser, 'RequestData')) {
                throw new ApplicationLogicException(
                    "You are not allowed to re-open a request that has been closed for over a week.");
            }
        }

        $request->setReserved(null);
        $request->setStatus($target);
        $request->setUpdateVersion(WebRequest::postInt('updateversion'));
        $request->save();

        $deto = $requestStates[$target]['deferto'];
        $detolog = $requestStates[$target]['defertolog'];

        Logger::deferRequest($database, $request, $detolog);

        $this->getNotificationHelper()->requestDeferred($request);
        SessionAlert::success("Request {$request->getId()} deferred to {$deto}");

        $this->redirect();
    }
}

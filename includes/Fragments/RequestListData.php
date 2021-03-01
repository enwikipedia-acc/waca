<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Fragments;

use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Pages\PageBan;
use Waca\Pages\RequestAction\PageBreakReservation;
use Waca\RequestList;
use Waca\SiteConfiguration;

trait RequestListData
{
    // function imports from InternalPageBase etc.
    protected abstract function getDatabase();

    protected abstract function getXffTrustProvider();

    /** @return SiteConfiguration */
    protected abstract function getSiteConfiguration();

    protected abstract function barrierTest($action, User $user, $pageName = null);

    /**
     * @param Request[] $requests
     *
     * @return RequestList
     */
    protected function prepareRequestData(array $requests) : RequestList
    {
        $requestList = new RequestList();
        $requestList->requests = $requests;

        $userIds = array_map(
            function(Request $entry) {
                return $entry->getReserved();
            },
            $requests
        );

        $requestList->userList = UserSearchHelper::get($this->getDatabase())->inIds($userIds)->fetchMap('username');

        $requestList->requestTrustedIp = [];
        $requestList->relatedIpRequests = [];
        $requestList->relatedEmailRequests = [];

        foreach ($requests as $request) {
            $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp(
                $request->getIp(),
                $request->getForwardedIp()
            );

            RequestSearchHelper::get($this->getDatabase())
                ->byIp($trustedIp)
                ->withConfirmedEmail()
                ->excludingPurgedData($this->getSiteConfiguration())
                ->excludingRequest($request->getId())
                ->getRecordCount($ipCount);

            RequestSearchHelper::get($this->getDatabase())
                ->byEmailAddress($request->getEmail())
                ->withConfirmedEmail()
                ->excludingPurgedData($this->getSiteConfiguration())
                ->excludingRequest($request->getId())
                ->getRecordCount($emailCount);

            $requestList->requestTrustedIp[$request->getId()] = $trustedIp;
            $requestList->relatedEmailRequests[$request->getId()] = $emailCount;
            $requestList->relatedIpRequests[$request->getId()] = $ipCount;

            $emailDomain = explode("@", $request->getEmail())[1];
            $requestList->commonEmail[$request->getId()] = in_array(strtolower($emailDomain), $this->getSiteConfiguration()->getCommonEmailDomains())
               || $request->getEmail() === $this->getSiteConfiguration()->getDataClearEmail();
        }

        $currentUser = User::getCurrent($this->getDatabase());

        $requestList->canBan = $this->barrierTest('set', $currentUser, PageBan::class);
        $requestList->canBreakReservation = $this->barrierTest('force', $currentUser, PageBreakReservation::class);
        $requestList->showPrivateData = $this->barrierTest('alwaysSeePrivateData', $currentUser, 'RequestData');
        $requestList->dataClearEmail = $this->getSiteConfiguration()->getDataClearEmail();
        $requestList->dataClearIp = $this->getSiteConfiguration()->getDataClearIp();

        return $requestList;
    }
}

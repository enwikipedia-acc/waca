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
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\Pages\RequestAction\PageBreakReservation;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\ILocationProvider;
use Waca\Providers\Interfaces\IRDnsProvider;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\RequestStatus;
use Waca\Security\SecurityManager;
use Waca\SiteConfiguration;
use Waca\WebRequest;

trait RequestData
{
    /** @return SiteConfiguration */
    protected abstract function getSiteConfiguration();

    /**
     * @var array Array of IP address classed as 'private' by RFC1918.
     */
    protected static $rfc1918ips = array(
        "10.0.0.0"    => "10.255.255.255",
        "172.16.0.0"  => "172.31.255.255",
        "192.168.0.0" => "192.168.255.255",
        "169.254.0.0" => "169.254.255.255",
        "127.0.0.0"   => "127.255.255.255",
    );

    /**
     * Gets a request object
     *
     * @param PdoDatabase $database  The database connection
     * @param int|null    $requestId The ID of the request to retrieve
     *
     * @return Request
     * @throws ApplicationLogicException
     */
    protected function getRequest(PdoDatabase $database, $requestId)
    {
        if ($requestId === null) {
            throw new ApplicationLogicException("No request specified");
        }

        $request = Request::getById($requestId, $database);
        if ($request === false || !is_a($request, Request::class)) {
            throw new ApplicationLogicException('Could not load the requested request!');
        }

        return $request;
    }

    /**
     * Returns a value stating whether the user is allowed to see private data or not
     *
     * @param Request $request
     * @param User    $currentUser
     *
     * @return bool
     * @category Security-Critical
     */
    protected function isAllowedPrivateData(Request $request, User $currentUser)
    {
        // Test the main security barrier for private data access using SecurityManager
        if ($this->barrierTest('alwaysSeePrivateData', $currentUser, 'RequestData')) {
            // Tool admins/check-users can always see private data
            return true;
        }

        // reserving user is allowed to see the data
        if ($currentUser->getId() === $request->getReserved()
            && $request->getReserved() !== null
            && $this->barrierTest('seePrivateDataWhenReserved', $currentUser, 'RequestData')
        ) {
            return true;
        }

        // user has the reveal hash
        if (WebRequest::getString('hash') === $request->getRevealHash()
            && $this->barrierTest('seePrivateDataWithHash', $currentUser, 'RequestData')
        ) {
            return true;
        }

        // nope. Not allowed.
        return false;
    }

    /**
     * Tests the security barrier for a specified action.
     *
     * Don't use within templates
     *
     * @param string      $action
     *
     * @param User        $user
     * @param null|string $pageName
     *
     * @return bool
     * @category Security-Critical
     */
    abstract protected function barrierTest($action, User $user, $pageName = null);

    /**
     * Gets the name of the route that has been passed from the request router.
     * @return string
     */
    abstract protected function getRouteName();

    /** @return SecurityManager */
    abstract protected function getSecurityManager();

    /**
     * Sets the name of the template this page should display.
     *
     * @param string $name
     */
    abstract protected function setTemplate($name);

    /** @return IXffTrustProvider */
    abstract protected function getXffTrustProvider();

    /** @return ILocationProvider */
    abstract protected function getLocationProvider();

    /** @return IRDnsProvider */
    abstract protected function getRdnsProvider();

    /**
     * Assigns a Smarty variable
     *
     * @param  array|string $name  the template variable name(s)
     * @param  mixed        $value the value to assign
     */
    abstract protected function assign($name, $value);

    /**
     * @param int|null    $requestReservationId
     * @param PdoDatabase $database
     * @param User        $currentUser
     */
    protected function setupReservationDetails($requestReservationId, PdoDatabase $database, User $currentUser)
    {
        $requestIsReserved = $requestReservationId !== null;
        $this->assign('requestIsReserved', $requestIsReserved);
        $this->assign('requestIsReservedByMe', false);

        if ($requestIsReserved) {
            $this->assign('requestReservedByName', User::getById($requestReservationId, $database)->getUsername());
            $this->assign('requestReservedById', $requestReservationId);

            if ($requestReservationId === $currentUser->getId()) {
                $this->assign('requestIsReservedByMe', true);
            }
        }

        $this->assign('canBreakReservation', $this->barrierTest('force', $currentUser, PageBreakReservation::class));
    }

    /**
     * Adds private request data to Smarty. DO NOT USE WITHOUT FIRST CHECKING THAT THE USER IS AUTHORISED!
     *
     * @param Request           $request
     * @param SiteConfiguration $configuration
     */
    protected function setupPrivateData(
        $request,
        SiteConfiguration $configuration
    ) {
        $xffProvider = $this->getXffTrustProvider();

        $this->assign('requestEmail', $request->getEmail());
        $emailDomain = explode("@", $request->getEmail())[1];
        $this->assign("emailurl", $emailDomain);
        $this->assign('commonEmailDomain', in_array(strtolower($emailDomain), $configuration->getCommonEmailDomains())
            || $request->getEmail() === $this->getSiteConfiguration()->getDataClearEmail());

        $trustedIp = $xffProvider->getTrustedClientIp($request->getIp(), $request->getForwardedIp());
        $this->assign('requestTrustedIp', $trustedIp);
        $this->assign('requestRealIp', $request->getIp());
        $this->assign('requestForwardedIp', $request->getForwardedIp());

        $trustedIpLocation = $this->getLocationProvider()->getIpLocation($trustedIp);
        $this->assign('requestTrustedIpLocation', $trustedIpLocation);

        $this->assign('requestHasForwardedIp', $request->getForwardedIp() !== null);

        $this->setupForwardedIpData($request);
    }

    /**
     * Adds related request data to Smarty. DO NOT USE WITHOUT FIRST CHECKING THAT THE USER IS AUTHORISED!
     *
     * @param Request           $request
     * @param SiteConfiguration $configuration
     * @param PdoDatabase       $database
     */
    protected function setupRelatedRequests(
        Request $request,
        SiteConfiguration $configuration,
        PdoDatabase $database)
    {
        $this->assign('canSeeRelatedRequests', true);

        $relatedEmailRequests = RequestSearchHelper::get($database)
            ->byEmailAddress($request->getEmail())
            ->withConfirmedEmail()
            ->excludingPurgedData($configuration)
            ->excludingRequest($request->getId())
            ->fetch();

        $this->assign('requestRelatedEmailRequestsCount', count($relatedEmailRequests));
        $this->assign('requestRelatedEmailRequests', $relatedEmailRequests);

        $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp($request->getIp(), $request->getForwardedIp());
        $relatedIpRequests = RequestSearchHelper::get($database)
            ->byIp($trustedIp)
            ->withConfirmedEmail()
            ->excludingPurgedData($configuration)
            ->excludingRequest($request->getId())
            ->fetch();

        $this->assign('requestRelatedIpRequestsCount', count($relatedIpRequests));
        $this->assign('requestRelatedIpRequests', $relatedIpRequests);
    }

    /**
     * Adds checkuser request data to Smarty. DO NOT USE WITHOUT FIRST CHECKING THAT THE USER IS AUTHORISED!
     *
     * @param Request $request
     */
    protected function setupCheckUserData(Request $request)
    {
        $this->assign('requestUserAgent', $request->getUserAgent());
    }

    /**
     * Sets up the basic data for this request, and adds it to Smarty
     *
     * @param Request           $request
     * @param SiteConfiguration $config
     */
    protected function setupBasicData(Request $request, SiteConfiguration $config)
    {
        $this->assign('requestId', $request->getId());
        $this->assign('updateVersion', $request->getUpdateVersion());
        $this->assign('requestName', $request->getName());
        $this->assign('requestDate', $request->getDate());
        $this->assign('requestStatus', $request->getStatus());

        $isClosed = !array_key_exists($request->getStatus(), $config->getRequestStates())
            && $request->getStatus() !== RequestStatus::HOSPITAL;
        $this->assign('requestIsClosed', $isClosed);
    }

    /**
     * Sets up the forwarded IP data for this request and adds it to Smarty
     *
     * @param Request $request
     */
    protected function setupForwardedIpData(Request $request)
    {
        if ($request->getForwardedIp() !== null) {
            $requestProxyData = array(); // Initialize array to store data to be output in Smarty template.
            $proxyIndex = 0;

            // Assuming [client] <=> [proxy1] <=> [proxy2] <=> [proxy3] <=> [us], we will see an XFF header of [client],
            // [proxy1], [proxy2], and our actual IP will be [proxy3]
            $proxies = explode(",", $request->getForwardedIp());
            $proxies[] = $request->getIp();

            // Origin is the supposed "client" IP.
            $origin = $proxies[0];
            $this->assign("forwardedOrigin", $origin);

            // We step through the servers in reverse order, from closest to furthest
            $proxies = array_reverse($proxies);

            // By default, we have trust, because the first in the chain is now REMOTE_ADDR, which is hardest to spoof.
            $trust = true;

            /**
             * @var int    $index     The zero-based index of the proxy.
             * @var string $proxyData The proxy IP address (although possibly not!)
             */
            foreach ($proxies as $index => $proxyData) {
                $proxyAddress = trim($proxyData);
                $requestProxyData[$proxyIndex]['ip'] = $proxyAddress;

                // get data on this IP.
                $thisProxyIsTrusted = $this->getXffTrustProvider()->isTrusted($proxyAddress);

                $proxyIsInPrivateRange = $this->getXffTrustProvider()
                    ->ipInRange(self::$rfc1918ips, $proxyAddress);

                if (!$proxyIsInPrivateRange) {
                    $proxyReverseDns = $this->getRdnsProvider()->getReverseDNS($proxyAddress);
                    $proxyLocation = $this->getLocationProvider()->getIpLocation($proxyAddress);
                }
                else {
                    // this is going to fail, so why bother trying?
                    $proxyReverseDns = false;
                    $proxyLocation = false;
                }

                // current trust chain status BEFORE this link
                $preLinkTrust = $trust;

                // is *this* link trusted? Note, this will be true even if there is an untrusted link before this!
                $requestProxyData[$proxyIndex]['trustedlink'] = $thisProxyIsTrusted;

                // set the trust status of the chain to this point
                $trust = $trust & $thisProxyIsTrusted;

                // If this is the origin address, and the chain was trusted before this point, then we can trust
                // the origin.
                if ($preLinkTrust && $proxyAddress == $origin) {
                    // if this is the origin, then we are at the last point in the chain.
                    // @todo: this is probably the cause of some bugs when an IP appears twice - we're missing a check
                    // to see if this is *really* the last in the chain, rather than just the same IP as it.
                    $trust = true;
                }

                $requestProxyData[$proxyIndex]['trust'] = $trust;

                $requestProxyData[$proxyIndex]['rdnsfailed'] = $proxyReverseDns === false;
                $requestProxyData[$proxyIndex]['rdns'] = $proxyReverseDns;
                $requestProxyData[$proxyIndex]['routable'] = !$proxyIsInPrivateRange;

                $requestProxyData[$proxyIndex]['location'] = $proxyLocation;

                if ($proxyReverseDns === $proxyAddress && $proxyIsInPrivateRange === false) {
                    $requestProxyData[$proxyIndex]['rdns'] = null;
                }

                $showLinks = (!$trust || $proxyAddress == $origin) && !$proxyIsInPrivateRange;
                $requestProxyData[$proxyIndex]['showlinks'] = $showLinks;

                $proxyIndex++;
            }

            $this->assign("requestProxyData", $requestProxyData);
        }
    }
}

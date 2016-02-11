<?php

namespace Pages;

use Comment;
use EmailTemplate;
use Exception;
use Log;
use Logger;
use PdoDatabase;
use Request;
use User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageViewRequest extends PageBase
{
	const STATUS_SYMBOL_OPEN = '&#x2610';
	const STATUS_SYMBOL_ACCEPTED = '&#x2611';
	const STATUS_SYMBOL_REJECTED = '&#x2612';
	/**
	 * @var array Array of IP address classed as 'private' by RFC1918.
	 */
	private static $rfc1918ips = array(
		"10.0.0.0"    => "10.255.255.255",
		"172.16.0.0"  => "172.31.255.255",
		"192.168.0.0" => "192.168.255.255",
		"169.254.0.0" => "169.254.255.255",
		"127.0.0.0"   => "127.255.255.255",
	);

	/**
	 * Main function for this page, when no specific actions are called.
	 * @throws ApplicationLogicException
	 */
	protected function main()
	{
		// get some useful objects
		$request = $this->getRequest();
		$config = $this->getSiteConfiguration();
		$database = $this->getDatabase();
		$currentUser = User::getCurrent();

		// Test we should be able to look at this request
		if ($config->getEmailConfirmationEnabled()) {
			if ($request->getEmailConfirm() !== 'Confirmed') {
				// Not allowed to look at this yet.
				throw new ApplicationLogicException('The email address has not yet been confirmed for this request.');
			}
		}

		$this->assign('requestId', $request->getId());
		$this->assign('requestName', $request->getName());
		$this->assign('requestDate', $request->getDate());
		$this->assign('requestStatus', $request->getStatus());

		$this->assign('requestIsClosed', !array_key_exists($request->getStatus(), $config->getRequestStates()));

		$this->setupUsernameData($request);

		$this->setupTitle($request);

		$this->setupReservationDetails($request->getReserved(), $database, $currentUser);
		$this->setupGeneralData($database);

		$this->assign('requestDataCleared', false);
		if ($request->getEmail() === $this->getSiteConfiguration()->getDataClearEmail()) {
			$this->assign('requestDataCleared', true);
		}

		$allowedPrivateData = true && $this->isAllowedPrivateData();

		$this->setupLogData($request, $database);

		if ($allowedPrivateData) {
			// todo: logging?

			$this->setupPrivateData($request, $currentUser);

			if ($currentUser->isCheckuser()) {
				$this->setupCheckUserData($request);
			}
		}
		else {
			$this->setTemplate('view-request/main.tpl');
		}
	}

	/**
	 * Gets a request object
	 *
	 * @return Request
	 * @throws ApplicationLogicException
	 */
	private function getRequest()
	{
		$requestId = WebRequest::getInt('id');
		if ($requestId === null) {
			throw new ApplicationLogicException("No request specified");
		}

		$database = $this->getDatabase();

		$request = Request::getById($requestId, $database);
		if (!is_a($request, Request::class)) {
			throw new ApplicationLogicException('Could not load the requested request!');
		}

		return $request;
	}

	/**
	 * @param Request $request
	 */
	protected function setupTitle(Request $request)
	{
		$statusSymbol = self::STATUS_SYMBOL_OPEN;
		if ($request->getStatus() === 'Closed') {
			if ($request->getWasCreated()) {
				$statusSymbol = self::STATUS_SYMBOL_ACCEPTED;
			}
			else {
				$statusSymbol = self::STATUS_SYMBOL_REJECTED;
			}
		}

		$this->setHtmlTitle($statusSymbol . ' #' . $request->getId());
	}

	/**
	 * @param int         $requestReservationId
	 * @param PdoDatabase $database
	 * @param User        $currentUser
	 */
	protected function setupReservationDetails($requestReservationId, PdoDatabase $database, User $currentUser)
	{
		$requestIsReserved = $requestReservationId != 0;
		$this->assign('requestIsReserved', $requestIsReserved);
		$this->assign('requestIsReservedByMe', false);

		if ($requestIsReserved) {
			$this->assign('requestReservedByName', User::getById($requestReservationId, $database)->getUsername());
			$this->assign('requestReservedById', $requestReservationId);

			if ($requestReservationId === $currentUser->getId()) {
				$this->assign('requestIsReservedByMe', true);
			}
		}
	}

	/**
	 * Sets up data unrelated to the request, such as the email template information
	 *
	 * @param PdoDatabase $database
	 */
	protected function setupGeneralData(PdoDatabase $database)
	{
		$config = $this->getSiteConfiguration();

		$this->assign('createAccountReason', 'Requested account at [[WP:ACC]], request #');

		$this->assign('defaultRequestState', $config->getDefaultRequestStateKey());

		$this->assign('requestStates', $config->getRequestStates());

		/** @var EmailTemplate $createdTemplate */
		$createdTemplate = EmailTemplate::getById($config->getDefaultCreatedTemplateId(), $database);

		$this->assign('createdHasJsQuestion', $createdTemplate->getJsquestion() != '');
		$this->assign('createdJsQuestion', $createdTemplate->getJsquestion());
		$this->assign('createdId', $createdTemplate->getId());
		$this->assign('createdName', $createdTemplate->getName());

		$createReasons = EmailTemplate::getActiveTemplates(EmailTemplate::CREATED);
		$this->assign("createReasons", $createReasons);
		$declineReasons = EmailTemplate::getActiveTemplates(EmailTemplate::NOT_CREATED);
		$this->assign("declineReasons", $declineReasons);

		$allCreateReasons = EmailTemplate::getAllActiveTemplates(EmailTemplate::CREATED);
		$this->assign("allCreateReasons", $allCreateReasons);
		$allDeclineReasons = EmailTemplate::getAllActiveTemplates(EmailTemplate::NOT_CREATED);
		$this->assign("allDeclineReasons", $allDeclineReasons);
		$allOtherReasons = EmailTemplate::getAllActiveTemplates(EmailTemplate::NONE);
		$this->assign("allOtherReasons", $allOtherReasons);

		$this->getTypeAheadHelper()->defineTypeAheadSource('username-typeahead', function() use($database) {
			return User::getAllUsernames($database, true);
		});
	}

	/**
	 * Returns a value stating whether the user is allowed to see private data or not
	 *
	 * @return bool
	 * @category Security-Critical
	 * @todo     Implement me!
	 */
	private function isAllowedPrivateData()
	{
		return true;
	}

	private function setupLogData(Request $request, PdoDatabase $database)
	{
		$logs = Logger::getRequestLogsWithComments($request->getId(), $database);
		$requestLogs = array();

		if (trim($request->getComment()) !== "") {
			$requestLogs[] = array(
				'type'     => 'comment',
				'security' => 'user',
				'userid'   => null,
				'user'     => $request->getName(),
				'entry'    => null,
				'time'     => $request->getDate(),
				'canedit'  => false,
				'id'       => $request->getId(),
				'comment'  => $request->getComment(),
			);
		}

		/** @var User[] $nameCache */
		$nameCache = array();

		$editableComments = false;
		if (User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser()) {
			$editableComments = true;
		}

		/** @var Log|Comment $entry */
		foreach ($logs as $entry) {
			// both log and comment have a 'user' field
			if (!array_key_exists($entry->getUser(), $nameCache)) {
				$entryUser = User::getById($entry->getUser(), $database);
				$nameCache[$entry->getUser()] = $entryUser;
			}

			if ($entry instanceof Comment) {
				$requestLogs[] = array(
					'type'     => 'comment',
					'security' => $entry->getVisibility(),
					'user'     => $nameCache[$entry->getUser()]->getUsername(),
					'userid'   => $entry->getUser() == -1 ? null : $entry->getUser(),
					'entry'    => null,
					'time'     => $entry->getTime(),
					'canedit'  => ($editableComments || $entry->getUser() == User::getCurrent()->getId()),
					'id'       => $entry->getId(),
					'comment'  => $entry->getComment(),
				);
			}

			if ($entry instanceof Log) {
				$requestLogs[] = array(
					'type'     => 'log',
					'security' => 'user',
					'userid'   => $entry->getUser() == -1 ? null : $entry->getUser(),
					'user'     => $nameCache[$entry->getUser()]->getUsername(),
					'entry'    => Logger::getLogDescription($entry),
					'time'     => $entry->getTimestamp(),
					'canedit'  => false,
					'id'       => $entry->getId(),
					'comment'  => $entry->getComment(),
				);
			}
		}

		$this->assign("requestLogs", $requestLogs);
	}

	/**
	 * @param Request $request
	 * @param User    $currentUser
	 */
	protected function setupPrivateData($request, User $currentUser)
	{
		$xffProvider = $this->getXffTrustProvider();
		$this->setTemplate('view-request/main-with-data.tpl');

		$relatedEmailRequests = $request->getRelatedEmailRequests();

		$this->assign('requestEmail', $request->getEmail());
		$this->assign('requestRelatedEmailRequestsCount', count($relatedEmailRequests));
		$this->assign('requestRelatedEmailRequests', $relatedEmailRequests);

		$trustedIp = $xffProvider->getTrustedClientIp($request->getIp(), $request->getForwardedIp());
		$this->assign('requestTrustedIp', $trustedIp);
		$this->assign('requestRealIp', $request->getIp());
		$this->assign('requestForwardedIp', $request->getForwardedIp());

		$trustedIpLocation = $this->getLocationProvider()->getIpLocation($trustedIp);
		$this->assign('requestTrustedIpLocation', $trustedIpLocation);

		$this->assign('requestHasForwardedIp', $request->getForwardedIp() != null);

		$this->assign('requestRelatedIpRequestsCount', count($relatedEmailRequests));
		$this->assign('requestRelatedIpRequests', $relatedEmailRequests);

		$this->assign('showRevealLink', false);
		if ($request->getReserved() === $currentUser->getId() ||
			$currentUser->isAdmin() ||
			$currentUser->isCheckuser()
		) {
			$this->assign('showRevealLink', true);
			$this->assign('revealHash', false); // @todo
		}

		$this->setupForwardedIpData($request);
	}

	private function setupForwardedIpData(Request $request)
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

				$proxyIsInPrivateRange = $this->getXffTrustProvider()->ipInRange(self::$rfc1918ips, $proxyAddress);

				if (!$proxyIsInPrivateRange) {
					$proxyReverseDns = $this->getRdnsProvider()->getRdns($proxyAddress);
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

				if ($proxyReverseDns === $proxyAddress && $proxyIsInPrivateRange == false) {
					$requestProxyData[$proxyIndex]['rdns'] = null;
				}

				$showLinks = (!$trust || $proxyAddress == $origin) && !$proxyIsInPrivateRange;
				$requestProxyData[$proxyIndex]['showlinks'] = $showLinks;

				$proxyIndex++;
			}

			$this->assign("requestProxyData", $requestProxyData);
		}
	}

	/**
	 * @param Request $request
	 */
	protected function setupCheckUserData(Request $request)
	{
		$this->setTemplate('view-request/main-with-checkuser-data.tpl');
		$this->assign('requestUserAgent', $request->getUserAgent());
	}

	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	protected function getSecurityConfiguration()
	{
		// @todo review me!
		return SecurityConfiguration::internalPage();
	}

	/**
	 * @param Request $request
	 */
	protected function setupUsernameData(Request $request)
	{
		$this->assign('requestIsBlacklisted', $request->isBlacklisted() !== false);
		$this->assign('requestBlacklist', $request->isBlacklisted());

		try {
			$spoofs = $this->getAntiSpoofProvider()->getSpoofs($request->getName());
		}
		catch (Exception $ex) {
			$spoofs = $ex->getMessage();
		}

		$this->assign("spoofs", $spoofs);
	}
}
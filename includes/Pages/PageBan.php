<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages;

use Exception;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Domain;
use Waca\DataObjects\Request;
use Waca\DataObjects\RequestQueue;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\BanHelper;
use Waca\Helpers\Logger;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageBan extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main(): void
    {
        $this->assignCSRFToken();
        $this->setHtmlTitle('Bans');

        $database = $this->getDatabase();
        $currentDomain = Domain::getCurrent($database);
        $bans = Ban::getActiveBans($database, $currentDomain->getId());

        $this->setupBanList($bans);

        $this->assign('isFiltered', false);
        $this->setTemplate('bans/main.tpl');
    }

    protected function show(): void
    {
        $this->assignCSRFToken();
        $this->setHtmlTitle('Bans');

        $rawIdList = WebRequest::getString('id');
        if ($rawIdList === null) {
            $this->redirect('bans');

            return;
        }

        $idList = explode(',', $rawIdList);

        $database = $this->getDatabase();
        $currentDomain = Domain::getCurrent($database);
        $bans = Ban::getByIdList($idList, $database, $currentDomain->getId());

        $this->setupBanList($bans);
        $this->assign('isFiltered', true);
        $this->setTemplate('bans/main.tpl');
    }

    /**
     * Entry point for the ban set action
     * @throws Smarty\Exception
     * @throws Exception
     */
    protected function set(): void
    {
        $this->setHtmlTitle('Bans');

        // dual-mode action
        if (WebRequest::wasPosted()) {
            try {
                $this->handlePostMethodForSetBan();
            }
            catch (ApplicationLogicException $ex) {
                SessionAlert::error($ex->getMessage());
                $this->redirect("bans", "set");
            }
        }
        else {
            $this->handleGetMethodForSetBan();

            $user = User::getCurrent($this->getDatabase());
            $banType = WebRequest::getString('type');
            $banRequest = WebRequest::getInt('request');

            // if the parameters are null, skip loading a request.
            if ($banType !== null && $banRequest !== null && $banRequest !== 0) {
                $this->preloadFormForRequest($banRequest, $banType, $user);
            }
        }
    }

    protected function replace(): void
    {
        $this->setHtmlTitle('Bans');

        $database = $this->getDatabase();
        $domain = Domain::getCurrent($database);

        // dual-mode action
        if (WebRequest::wasPosted()) {
            try {
                $originalBanId = WebRequest::postInt('replaceBanId');
                $originalBanUpdateVersion = WebRequest::postInt('replaceBanUpdateVersion');

                $originalBan = Ban::getActiveId($originalBanId, $database, $domain->getId());

                if ($originalBan === false) {
                    throw new ApplicationLogicException("The specified ban is not currently active, or doesn't exist.");
                }

                // Discard original ban; we're replacing it.
                $originalBan->setUpdateVersion($originalBanUpdateVersion);
                $originalBan->setActive(false);
                $originalBan->save();

                Logger::banReplaced($database, $originalBan);

                // Proceed as normal to save the new ban.
                $this->handlePostMethodForSetBan();
            }
            catch (ApplicationLogicException $ex) {
                $database->rollback();
                SessionAlert::error($ex->getMessage());
                $this->redirect("bans", "set");
            }
        }
        else {
            $this->handleGetMethodForSetBan();

            $user = User::getCurrent($database);
            $originalBanId = WebRequest::getString('id');

            $originalBan = Ban::getActiveId($originalBanId, $database, $domain->getId());

            if ($originalBan === false) {
                throw new ApplicationLogicException("The specified ban is not currently active, or doesn't exist.");
            }

            if ($originalBan->getName() !== null) {
                if (!$this->barrierTest('name', $user, 'BanType')) {
                    SessionAlert::error("You are not allowed to set this type of ban.");
                    $this->redirect("bans", "set");
                    return;
                }

                $this->assign('banName', $originalBan->getName());
            }

            if ($originalBan->getEmail() !== null) {
                if (!$this->barrierTest('email', $user, 'BanType')) {
                    SessionAlert::error("You are not allowed to set this type of ban.");
                    $this->redirect("bans", "set");
                    return;
                }

                $this->assign('banEmail', $originalBan->getEmail());
            }

            if ($originalBan->getUseragent() !== null) {
                if (!$this->barrierTest('useragent', $user, 'BanType')) {
                    SessionAlert::error("You are not allowed to set this type of ban.");
                    $this->redirect("bans", "set");
                    return;
                }

                $this->assign('banUseragent', $originalBan->getUseragent());
            }

            if ($originalBan->getIp() !== null) {
                if (!$this->barrierTest('ip', $user, 'BanType')) {
                    SessionAlert::error("You are not allowed to set this type of ban.");
                    $this->redirect("bans", "set");
                    return;
                }

                $this->assign('banIP', $originalBan->getIp() . '/' . $originalBan->getIpMask());
            }

            $banIsGlobal = $originalBan->getDomain() === null;
            if ($banIsGlobal) {
                if (!$this->barrierTest('global', $user, 'BanType')) {
                    SessionAlert::error("You are not allowed to set this type of ban.");
                    $this->redirect("bans", "set");
                    return;
                }
            }

            if (!$this->barrierTest($originalBan->getVisibility(), $user, 'BanVisibility')) {
                SessionAlert::error("You are not allowed to set this type of ban.");
                $this->redirect("bans", "set");
                return;
            }

            $this->assign('banGlobal', $banIsGlobal);
            $this->assign('banVisibility', $originalBan->getVisibility());

            if ($originalBan->getDuration() !== null) {
                $this->assign('banDuration', date('c', $originalBan->getDuration()));
            }

            $this->assign('banReason', $originalBan->getReason());
            $this->assign('banAction', $originalBan->getAction());
            $this->assign('banQueue', $originalBan->getTargetQueue());

            $this->assign('replaceBanId', $originalBan->getId());
            $this->assign('replaceBanUpdateVersion', $originalBan->getUpdateVersion());
        }
    }

    /**
     * Entry point for the ban remove action
     *
     * @throws AccessDeniedException
     * @throws ApplicationLogicException
     * @throws Smarty\Exception
     */
    protected function remove(): void
    {
        $this->setHtmlTitle('Bans');

        $ban = $this->getBanForUnban();

        $banHelper = new BanHelper($this->getDatabase(), $this->getXffTrustProvider(), $this->getSecurityManager());
        if (!$banHelper->canUnban($ban)) {
            // triggered when a user tries to unban a ban they can't see the entirety of.
            // there's no UI way to get to this, so a raw exception is fine.
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        // dual mode
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $unbanReason = WebRequest::postString('unbanreason');

            if ($unbanReason === null || trim($unbanReason) === "") {
                SessionAlert::error('No unban reason specified');
                $this->redirect("bans", "remove", array('id' => $ban->getId()));
            }

            // set optimistic locking from delete form page load
            $updateVersion = WebRequest::postInt('updateversion');
            $ban->setUpdateVersion($updateVersion);

            $database = $this->getDatabase();
            $ban->setActive(false);
            $ban->save();

            Logger::unbanned($database, $ban, $unbanReason);

            SessionAlert::quick('Disabled ban.');
            $this->getNotificationHelper()->unbanned($ban, $unbanReason);

            $this->redirect('bans');
        }
        else {
            $this->assignCSRFToken();
            $this->assign('ban', $ban);
            $this->setTemplate('bans/unban.tpl');
        }
    }

    /**
     * Retrieves the requested ban duration from the WebRequest
     *
     * @throws ApplicationLogicException
     */
    private function getBanDuration(): ?int
    {
        $duration = WebRequest::postString('duration');
        if ($duration === "other") {
            $duration = strtotime(WebRequest::postString('otherduration'));

            if (!$duration) {
                throw new ApplicationLogicException('Invalid ban time');
            }
            elseif (time() > $duration) {
                throw new ApplicationLogicException('Ban time has already expired!');
            }

            return $duration;
        }
        elseif ($duration === "-1") {
            return null;
        }
        else {
            return WebRequest::postInt('duration') + time();
        }
    }

    /**
     * Handles the POST method on the set action
     *
     * @throws ApplicationLogicException
     * @throws Exception
     */
    private function handlePostMethodForSetBan()
    {
        $this->validateCSRFToken();
        $database = $this->getDatabase();
        $user = User::getCurrent($database);
        $currentDomain = Domain::getCurrent($database);

        // Checks whether there is a reason entered for ban.
        $reason = WebRequest::postString('banreason');
        if ($reason === null || trim($reason) === "") {
            throw new ApplicationLogicException('You must specify a ban reason');
        }

        // ban targets
        list($targetName, $targetIp, $targetEmail, $targetUseragent) = $this->getRawBanTargets($user);

        $visibility = $this->getBanVisibility();

        // Validate ban duration
        $duration = $this->getBanDuration();

        $action = WebRequest::postString('banAction') ?? Ban::ACTION_NONE;

        $global = WebRequest::postBoolean('banGlobal');
        if (!$this->barrierTest('global', $user, 'BanType')) {
            $global = false;
        }

        if ($action === Ban::ACTION_DEFER && $global) {
            throw new ApplicationLogicException("Cannot set a global ban in defer-to-queue mode.");
        }

        // handle CIDR ranges
        $targetMask = null;
        if ($targetIp !== null) {
            list($targetIp, $targetMask) = $this->splitCidrRange($targetIp);
            $this->validateIpBan($targetIp, $targetMask, $user, $action);
        }

        $banHelper = new BanHelper($this->getDatabase(), $this->getXffTrustProvider(), $this->getSecurityManager());

        $bansByTarget = $banHelper->getBansByTarget(
            $targetName,
            $targetEmail,
            $targetIp,
            $targetMask,
            $targetUseragent,
            $currentDomain->getId());

        if (count($bansByTarget) > 0) {
            throw new ApplicationLogicException('This target is already banned!');
        }

        $ban = new Ban();
        $ban->setDatabase($database);
        $ban->setActive(true);

        $ban->setName($targetName);
        $ban->setIp($targetIp, $targetMask);
        $ban->setEmail($targetEmail);
        $ban->setUseragent($targetUseragent);

        $ban->setUser($user->getId());
        $ban->setReason($reason);
        $ban->setDuration($duration);
        $ban->setVisibility($visibility);

        $ban->setDomain($global ? null : $currentDomain->getId());

        $ban->setAction($action);
        if ($ban->getAction() === Ban::ACTION_DEFER) {
            //FIXME: domains
            $queue = RequestQueue::getByApiName($database, WebRequest::postString('banActionTarget'), 1);
            if ($queue === false) {
                throw new ApplicationLogicException("Unknown target queue");
            }

            if (!$queue->isEnabled()) {
                throw new ApplicationLogicException("Target queue is not enabled");
            }

            $ban->setTargetQueue($queue->getId());
        }

        $ban->save();

        Logger::banned($database, $ban, $reason);

        $this->getNotificationHelper()->banned($ban);
        SessionAlert::quick('Ban has been set.');

        $this->redirect('bans');
    }

    /**
     * Handles the GET method on the set action
     * @throws Exception
     */
    private function handleGetMethodForSetBan()
    {
        $this->setTemplate('bans/banform.tpl');
        $this->assignCSRFToken();

        $this->assign('maxIpRange', $this->getSiteConfiguration()->getBanMaxIpRange());
        $this->assign('maxIpBlockRange', $this->getSiteConfiguration()->getBanMaxIpBlockRange());

        $this->assign('banVisibility', 'user');
        $this->assign('banGlobal', false);
        $this->assign('banQueue', false);
        $this->assign('banAction', Ban::ACTION_BLOCK);
        $this->assign('banDuration', '');
        $this->assign('banReason', '');

        $this->assign('banEmail', '');
        $this->assign('banIP', '');
        $this->assign('banName', '');
        $this->assign('banUseragent', '');

        $this->assign('replaceBanId', null);



        $database = $this->getDatabase();

        $user = User::getCurrent($database);
        $this->setupSecurity($user);

        $queues = RequestQueue::getEnabledQueues($database);

        $this->assign('requestQueues', $queues);
    }

    /**
     * Finds the Ban object referenced in the WebRequest if it is valid
     *
     * @return Ban
     * @throws ApplicationLogicException
     */
    private function getBanForUnban(): Ban
    {
        $banId = WebRequest::getInt('id');
        if ($banId === null || $banId === 0) {
            throw new ApplicationLogicException("The ban ID appears to be missing. This is probably a bug.");
        }

        $database = $this->getDatabase();
        $this->setupSecurity(User::getCurrent($database));
        $currentDomain = Domain::getCurrent($database);
        $ban = Ban::getActiveId($banId, $database, $currentDomain->getId());

        if ($ban === false) {
            throw new ApplicationLogicException("The specified ban is not currently active, or doesn't exist.");
        }

        return $ban;
    }

    /**
     * Sets up Smarty variables for access control
     */
    private function setupSecurity(User $user): void
    {
        $this->assign('canSeeIpBan', $this->barrierTest('ip', $user, 'BanType'));
        $this->assign('canSeeNameBan', $this->barrierTest('name', $user, 'BanType'));
        $this->assign('canSeeEmailBan', $this->barrierTest('email', $user, 'BanType'));
        $this->assign('canSeeUseragentBan', $this->barrierTest('useragent', $user, 'BanType'));

        $this->assign('canGlobalBan', $this->barrierTest('global', $user, 'BanType'));

        $this->assign('canSeeUserVisibility', $this->barrierTest('user', $user, 'BanVisibility'));
        $this->assign('canSeeAdminVisibility', $this->barrierTest('admin', $user, 'BanVisibility'));
        $this->assign('canSeeCheckuserVisibility', $this->barrierTest('checkuser', $user, 'BanVisibility'));
    }

    /**
     * Validates that the provided IP is acceptable for a ban of this type
     *
     * @param string $targetIp   IP address
     * @param int    $targetMask CIDR prefix length
     * @param User   $user       User performing the ban
     * @param string $action     Ban action to take
     *
     * @throws ApplicationLogicException
     */
    private function validateIpBan(string $targetIp, int $targetMask, User $user, string $action): void
    {
        // validate this is an IP
        if (!filter_var($targetIp, FILTER_VALIDATE_IP)) {
            throw new ApplicationLogicException("Not a valid IP address");
        }

        $canLargeIpBan = $this->barrierTest('ip-largerange', $user, 'BanType');
        $maxIpBlockRange = $this->getSiteConfiguration()->getBanMaxIpBlockRange();
        $maxIpRange = $this->getSiteConfiguration()->getBanMaxIpRange();

        // validate CIDR ranges
        if (filter_var($targetIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if ($targetMask < 0 || $targetMask > 128) {
                throw new ApplicationLogicException("CIDR mask out of range for IPv6");
            }

            // prevent setting the ban if:
            //  * the user isn't allowed to set large bans, AND
            //  * the ban is a drop or a block (preventing human review of the request), AND
            //  * the mask is too wide-reaching
            if (!$canLargeIpBan && ($action == Ban::ACTION_BLOCK || $action == Ban::ACTION_DROP) && $targetMask < $maxIpBlockRange[6]) {
                throw new ApplicationLogicException("The requested IP range for this ban is too wide for the block/drop action.");
            }

            if (!$canLargeIpBan && $targetMask < $maxIpRange[6]) {
                throw new ApplicationLogicException("The requested IP range for this ban is too wide.");
            }
        }

        if (filter_var($targetIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if ($targetMask < 0 || $targetMask > 32) {
                throw new ApplicationLogicException("CIDR mask out of range for IPv4");
            }

            if (!$canLargeIpBan && ($action == Ban::ACTION_BLOCK || $action == Ban::ACTION_DROP) && $targetMask < $maxIpBlockRange[4]) {
                throw new ApplicationLogicException("The IP range for this ban is too wide for the block/drop action.");
            }

            if (!$canLargeIpBan && $targetMask < $maxIpRange[4]) {
                throw new ApplicationLogicException("The requested IP range for this ban is too wide.");
            }
        }

        $squidIpList = $this->getSiteConfiguration()->getSquidList();
        if (in_array($targetIp, $squidIpList)) {
            throw new ApplicationLogicException("This IP address is on the protected list of proxies, and cannot be banned.");
        }
    }

    /**
     * Configures a ban list template for display
     *
     * @param Ban[] $bans
     */
    private function setupBanList(array $bans): void
    {
        $userIds = array_map(fn(Ban $entry) => $entry->getUser(), $bans);
        $userList = UserSearchHelper::get($this->getDatabase())->inIds($userIds)->fetchMap('username');

        $domainIds = array_filter(array_unique(array_map(fn(Ban $entry) => $entry->getDomain(), $bans)));
        $domains = [];
        foreach ($domainIds as $d) {
            if ($d === null) {
                continue;
            }
            $domains[$d] = Domain::getById($d, $this->getDatabase());
        }

        $this->assign('domains', $domains);

        $user = User::getCurrent($this->getDatabase());
        $this->assign('canSet', $this->barrierTest('set', $user));
        $this->assign('canRemove', $this->barrierTest('remove', $user));

        $this->setupSecurity($user);

        $this->assign('usernames', $userList);
        $this->assign('activebans', $bans);

        $banHelper = new BanHelper($this->getDatabase(), $this->getXffTrustProvider(), $this->getSecurityManager());
        $this->assign('banHelper', $banHelper);
    }

    /**
     * Converts a plain IP or CIDR mask into an IP and a CIDR suffix
     *
     * @param string $targetIp IP or CIDR range
     *
     * @return array
     */
    private function splitCidrRange(string $targetIp): array
    {
        if (strpos($targetIp, '/') !== false) {
            $ipParts = explode('/', $targetIp, 2);
            $targetIp = $ipParts[0];
            $targetMask = (int)$ipParts[1];
        }
        else {
            // Default the CIDR range based on the IP type
            $targetMask = filter_var($targetIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 128 : 32;
        }

        return array($targetIp, $targetMask);
}

    /**
     * Returns the validated ban visibility from WebRequest
     *
     * @throws ApplicationLogicException
     */
    private function getBanVisibility(): string
    {
        $visibility = WebRequest::postString('banVisibility');
        if ($visibility !== 'user' && $visibility !== 'admin' && $visibility !== 'checkuser') {
            throw new ApplicationLogicException('Invalid ban visibility');
        }

        return $visibility;
    }

    /**
     * Returns array of [username, ip, email, ua] as ban targets from WebRequest,
     * filtered for whether the user is allowed to set bans including those types.
     *
     * @return string[]
     * @throws ApplicationLogicException
     */
    private function getRawBanTargets(User $user): array
    {
        $targetName = WebRequest::postString('banName');
        $targetIp = WebRequest::postString('banIP');
        $targetEmail = WebRequest::postString('banEmail');
        $targetUseragent = WebRequest::postString('banUseragent');

        // check the user is allowed to use provided targets
        if (!$this->barrierTest('name', $user, 'BanType')) {
            $targetName = null;
        }
        if (!$this->barrierTest('ip', $user, 'BanType')) {
            $targetIp = null;
        }
        if (!$this->barrierTest('email', $user, 'BanType')) {
            $targetEmail = null;
        }
        if (!$this->barrierTest('useragent', $user, 'BanType')) {
            $targetUseragent = null;
        }

        // Checks whether there is a target entered to ban.
        if ($targetName === null && $targetIp === null && $targetEmail === null && $targetUseragent === null) {
            throw new ApplicationLogicException('You must specify a target to be banned');
        }

        return array($targetName, $targetIp, $targetEmail, $targetUseragent);
    }

    private function preloadFormForRequest(int $banRequest, string $banType, User $user): void
    {
        $database = $this->getDatabase();

        // Attempt to resolve the correct target
        /** @var Request|false $request */
        $request = Request::getById($banRequest, $database);
        if ($request === false) {
            $this->assign('bantarget', '');

            return;
        }

        switch ($banType) {
            case 'EMail':
                if ($this->barrierTest('email', $user, 'BanType')) {
                    $this->assign('banEmail', $request->getEmail());
                }
                break;
            case 'IP':
                if ($this->barrierTest('ip', $user, 'BanType')) {
                    $trustedIp = $this->getXffTrustProvider()->getTrustedClientIp(
                        $request->getIp(),
                        $request->getForwardedIp());

                    $this->assign('banIP', $trustedIp);
                }
                break;
            case 'Name':
                if ($this->barrierTest('name', $user, 'BanType')) {
                    $this->assign('banName', $request->getName());
                }
                break;
            case 'UA':
                if ($this->barrierTest('useragent', $user, 'BanType')) {
                    $this->assign('banUseragent', $request->getEmail());
                }
                break;
        }
    }
}

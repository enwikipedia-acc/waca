<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Exception;
use SmartyException;
use Waca\DataObjects\Ban;
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
    protected function main()
    {
        $this->assignCSRFToken();
        $this->setHtmlTitle('Bans');

        $bans = Ban::getActiveBans($this->getDatabase());

        $this->setupBanList($bans);

        $this->assign('isFiltered', false);
        $this->setTemplate('bans/main.tpl');
    }

    protected function show()
    {
        $this->assignCSRFToken();
        $this->setHtmlTitle('Bans');

        $rawIdList = WebRequest::getString('id');
        if ($rawIdList === null) {
            $this->redirect('bans');

            return;
        }

        $idList = explode(',', $rawIdList);

        $bans = Ban::getByIdList($idList, $this->getDatabase());

        $this->setupBanList($bans);
        $this->assign('isFiltered', true);
        $this->setTemplate('bans/main.tpl');
    }

    /**
     * Entry point for the ban set action
     * @throws SmartyException
     * @throws Exception
     */
    protected function set()
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
        }
    }

    /**
     * Entry point for the ban remove action
     *
     * @throws AccessDeniedException
     * @throws ApplicationLogicException
     * @throws SmartyException
     */
    protected function remove()
    {
        $this->setHtmlTitle('Bans');

        $ban = $this->getBanForUnban();

        $banHelper = new BanHelper($this->getDatabase(), $this->getXffTrustProvider(), $this->getSecurityManager());
        if (!$banHelper->canUnban($ban)) {
            // triggered when a user tries to unban a ban they can't see the entirety of.
            // there's no UI way to get to this, so a raw exception is fine.
            throw new AccessDeniedException($this->getSecurityManager());
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
     * @throws ApplicationLogicException
     */
    private function getBanDuration()
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
            $duration = WebRequest::postInt('duration') + time();

            return $duration;
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

        // handle CIDR ranges
        $targetMask = null;
        if ($targetIp !== null) {
            list($targetIp, $targetMask) = $this->splitCidrRange($targetIp);
            $this->validateIpBan($targetIp, $targetMask, $user, $action);
        }

        $banHelper = new BanHelper($this->getDatabase(), $this->getXffTrustProvider(), $this->getSecurityManager());
        if (count($banHelper->getBansByTarget($targetName, $targetEmail, $targetIp, $targetMask, $targetUseragent)) > 0) {
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
    protected function handleGetMethodForSetBan()
    {
        $this->setTemplate('bans/banform.tpl');
        $this->assignCSRFToken();

        $this->assign('maxIpRange', $this->getSiteConfiguration()->getBanMaxIpRange());
        $this->assign('maxIpBlockRange', $this->getSiteConfiguration()->getBanMaxIpBlockRange());

        $database = $this->getDatabase();

        $user = User::getCurrent($database);
        $this->setupSecurity($user);

        $queues = RequestQueue::getEnabledQueues($database);

        $this->assign('requestQueues', $queues);

        $banType = WebRequest::getString('type');
        $banRequest = WebRequest::getInt('request');

        // if the parameters are null, skip loading a request.
        if ($banType === null || $banRequest === null || $banRequest === 0) {
            return;
        }

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
                    $this->assign('banIP', $this->getXffTrustProvider()
                        ->getTrustedClientIp($request->getIp(), $request->getForwardedIp()));
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

    /**
     * @return Ban
     * @throws ApplicationLogicException
     */
    private function getBanForUnban()
    {
        $banId = WebRequest::getInt('id');
        if ($banId === null || $banId === 0) {
            throw new ApplicationLogicException("The ban ID appears to be missing. This is probably a bug.");
        }

        $database = $this->getDatabase();
        $this->setupSecurity(User::getCurrent($database));
        $ban = Ban::getActiveId($banId, $database);

        if ($ban === false) {
            throw new ApplicationLogicException("The specified ban is not currently active, or doesn't exist.");
        }

        return $ban;
    }

    /**
     * @param $user
     */
    protected function setupSecurity($user): void
    {
        $this->assign('canSeeIpBan', $this->barrierTest('ip', $user, 'BanType'));
        $this->assign('canSeeNameBan', $this->barrierTest('name', $user, 'BanType'));
        $this->assign('canSeeEmailBan', $this->barrierTest('email', $user, 'BanType'));
        $this->assign('canSeeUseragentBan', $this->barrierTest('useragent', $user, 'BanType'));

        $this->assign('canSeeUserVisibility', $this->barrierTest('user', $user, 'BanVisibility'));
        $this->assign('canSeeAdminVisibility', $this->barrierTest('admin', $user, 'BanVisibility'));
        $this->assign('canSeeCheckuserVisibility', $this->barrierTest('checkuser', $user, 'BanVisibility'));
    }

    /**
     * @param string $targetIp
     * @param        $targetMask
     * @param User   $user
     * @param        $action
     *
     * @throws ApplicationLogicException
     */
    private function validateIpBan(string $targetIp, $targetMask, User $user, $action): void
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
     * @param array $bans
     */
    protected function setupBanList(array $bans): void
    {
        $userIds = array_map(
            function(Ban $entry) {
                return $entry->getUser();
            },
            $bans);
        $userList = UserSearchHelper::get($this->getDatabase())->inIds($userIds)->fetchMap('username');

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
     * @param string $targetIp
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
            $targetMask = filter_var($targetIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 128 : 32;
        }

        return array($targetIp, $targetMask);
}

    /**
     * @return string|null
     * @throws ApplicationLogicException
     */
    private function getBanVisibility()
    {
        $visibility = WebRequest::postString('banVisibility');
        if ($visibility !== 'user' && $visibility !== 'admin' && $visibility !== 'checkuser') {
            throw new ApplicationLogicException('Invalid ban visibility');
        }

        return $visibility;
    }

    /**
     * @param $user
     *
     * @return array
     * @throws ApplicationLogicException
     */
    private function getRawBanTargets($user): array
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
}

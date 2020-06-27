<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use PDO;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Helpers\Interfaces\IBanHelper;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\Security\SecurityManager;

class BanHelper implements IBanHelper
{
    /** @var PdoDatabase */
    private $database;
    /** @var IXffTrustProvider */
    private $xffTrustProvider;
    /** @var Ban[][] */
    private $banCache = [];
    /**
     * @var null|SecurityManager
     */
    private $securityManager;

    public function __construct(
        PdoDatabase $database,
        IXffTrustProvider $xffTrustProvider,
        ?SecurityManager $securityManager
    ) {
        $this->database = $database;
        $this->xffTrustProvider = $xffTrustProvider;
        $this->securityManager = $securityManager;
    }

    public function isBlockBanned(Request $request): bool
    {
        if (!isset($this->banCache[$request->getId()])) {
            $this->banCache[$request->getId()] = $this->getBansForRequestFromDatabase($request);
        }

        foreach($this->banCache[$request->getId()] as $ban) {
            if($ban->getAction() === Ban::ACTION_BLOCK) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return Ban[]
     */
    public function getBans(Request $request): array
    {
        if (!isset($this->banCache[$request->getId()])) {
            $this->banCache[$request->getId()] = $this->getBansForRequestFromDatabase($request);
        }

        return $this->banCache[$request->getId()];
    }

    public function getBansByTarget(?string $name, ?string $email, ?string $ip, ?int $mask, ?string $useragent)
    {
        /** @noinspection SqlConstantCondition */
        $query = <<<SQL
SELECT * FROM ban 
WHERE 1 = 1
  AND ((name is null and :nname is null) OR name = :name)
  AND ((email is null and :nemail is null) OR email = :email)
  AND ((useragent is null and :nuseragent is null) OR useragent = :useragent)
  AND ((ip is null and :nip is null) OR ip = inet6_aton(:ip))
  AND ((ipmask is null and :nipmask is null) OR ipmask = :ipmask)
  AND (duration > UNIX_TIMESTAMP() OR duration is null) 
  AND active = 1;
SQL;

        $statement = $this->database->prepare($query);
        $statement->execute([
            ':name'       => $name,
            ':nname'      => $name,
            ':email'      => $email,
            ':nemail'     => $email,
            ':ip'         => $ip,
            ':nip'        => $ip,
            ':ipmask'     => $mask,
            ':nipmask'    => $mask,
            ':useragent'  => $useragent,
            ':nuseragent' => $useragent,
        ]);

        $result = array();

        /** @var Ban $v */
        foreach ($statement->fetchAll(PDO::FETCH_CLASS, Ban::class) as $v) {
            $v->setDatabase($this->database);
            $result[] = $v;
        }

        return $result;
    }

    public function isActive(Ban $ban): bool
    {
        if (!$ban->isActive()) {
            return false;
        }

        if ($ban->getDuration() !== null && $ban->getDuration() < time()) {
            return false;
        }

        return true;
    }

    public function canUnban(Ban $ban): bool
    {
        if ($this->securityManager === null) {
            return false;
        }

        if (!$this->isActive($ban)) {
            return false;
        }

        $user = User::getCurrent($this->database);

        $allowed = true;
        $allowed &= ($ban->getName() === null || $this->securityManager->allows('BanType', 'name',
                $user) === SecurityManager::ALLOWED);
        $allowed &= ($ban->getEmail() === null || $this->securityManager->allows('BanType', 'email',
                $user) === SecurityManager::ALLOWED);
        $allowed &= ($ban->getIp() === null || $this->securityManager->allows('BanType', 'ip',
                $user) === SecurityManager::ALLOWED);
        $allowed &= ($ban->getUseragent() === null || $this->securityManager->allows('BanType', 'useragent',
                $user) === SecurityManager::ALLOWED);

        return $allowed;
    }

    /**
     * @param Request $request
     *
     * @return Ban[]
     */
    private function getBansForRequestFromDatabase(Request $request): array
    {
        /** @noinspection SqlConstantCondition - included for clarity of code */
        $query = <<<SQL
select b.* from ban b
left join netmask n on 1 = 1
    and n.cidr = b.ipmask
    and n.protocol = case length(b.ip) when 4 then 4 when 16 then 6 end
where 1 = 1
    and coalesce(:name rlike name, true)
    and coalesce(:email rlike email, true)
    and coalesce(:useragent rlike useragent, true)
    and case
        when length(b.ip) = 4 then
          (conv(hex(b.ip), 16, 10) & n.maskl) = (conv(hex(inet6_aton(:ip4)), 16, 10) & n.maskl)
        when length(b.ip) = 16 then
            (conv(left(hex(b.ip), 16), 16, 10) & n.maskh) = (conv(left(hex(inet6_aton(:ip6h)), 16), 16, 10) & n.maskh)
            and (conv(right(hex(b.ip), 16), 16, 10) & n.maskl) = (conv(right(hex(inet6_aton(:ip6l)), 16), 16, 10) & n.maskl)
        when length(b.ip) is null then true
    end
    and active = 1
    and (duration > UNIX_TIMESTAMP() or duration is null)
SQL;

        $statement = $this->database->prepare($query);
        $trustedIp = $this->xffTrustProvider->getTrustedClientIp($request->getIp(), $request->getForwardedIp());

        $statement->execute([
            ':name'      => $request->getName(),
            ':email'     => $request->getEmail(),
            ':useragent' => $request->getUserAgent(),
            ':ip4'       => $trustedIp,
            ':ip6h'      => $trustedIp,
            ':ip6l'      => $trustedIp,
        ]);

        /** @var Ban[] $result */
        $result = [];

        /** @var Ban $v */
        foreach ($statement->fetchAll(PDO::FETCH_CLASS, Ban::class) as $v) {
            $v->setDatabase($this->database);
            $result[] = $v;
        }

        return $result;
    }
}

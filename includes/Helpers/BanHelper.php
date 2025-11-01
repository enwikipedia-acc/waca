<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers;

use PDO;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Domain;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Helpers\Interfaces\IBanHelper;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\Security\ISecurityManager;

class BanHelper implements IBanHelper
{
    /** @var PdoDatabase */
    private $database;
    /** @var IXffTrustProvider */
    private $xffTrustProvider;
    /** @var Ban[][] */
    private $banCache = [];

    private ?ISecurityManager $securityManager;

    public function __construct(
        PdoDatabase $database,
        IXffTrustProvider $xffTrustProvider,
        ?ISecurityManager $securityManager
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

        foreach ($this->banCache[$request->getId()] as $ban) {
            if ($ban->getAction() === Ban::ACTION_BLOCK) {
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

    public function getBansByTarget(
        ?string $name,
        ?string $email,
        ?string $ip,
        ?int $mask,
        ?string $useragent,
        int $domain
    ) {
        /** @noinspection SqlConstantCondition */
        $query = <<<SQL
SELECT * FROM ban 
WHERE 1 = 1
  AND ((name IS NULL AND :nname IS NULL) OR name = :name)
  AND ((email IS NULL AND :nemail IS NULL) OR email = :email)
  AND ((useragent IS NULL AND :nuseragent IS NULL) OR useragent = :useragent)
  AND ((ip IS NULL AND :nip IS NULL) OR ip = INET6_ATON(:ip))
  AND ((ipmask IS NULL AND :nipmask IS NULL) OR ipmask = :ipmask)
  AND (duration > UNIX_TIMESTAMP() OR duration IS NULL) 
  AND active = 1
  AND (domain IS NULL OR domain = :domain);
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
            ':domain'     => $domain,
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
        $allowed = $allowed && ($ban->getName() === null || $this->securityManager->allows('BanType', 'name', $user) === ISecurityManager::ALLOWED);
        $allowed = $allowed && ($ban->getEmail() === null || $this->securityManager->allows('BanType', 'email', $user) === ISecurityManager::ALLOWED);
        $allowed = $allowed && ($ban->getIp() === null || $this->securityManager->allows('BanType', 'ip', $user) === ISecurityManager::ALLOWED);
        $allowed = $allowed && ($ban->getUseragent() === null || $this->securityManager->allows('BanType', 'useragent', $user) === ISecurityManager::ALLOWED);

        if ($ban->getDomain() === null) {
            $allowed &= $this->securityManager->allows('BanType', 'global', $user) === ISecurityManager::ALLOWED;
        }
        else {
            $currentDomain = Domain::getCurrent($this->database);
            $allowed &= $currentDomain->getId() === $ban->getDomain();
        }

        $allowed = $allowed && $this->securityManager->allows('BanVisibility', $ban->getVisibility(), $user) === ISecurityManager::ALLOWED;

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
SELECT b.* FROM ban b
LEFT JOIN netmask n ON 1 = 1
    AND n.cidr = b.ipmask
    AND n.protocol = CASE LENGTH(b.ip) WHEN 4 THEN 4 WHEN 16 THEN 6 END
WHERE 1 = 1
    AND COALESCE(:name RLIKE name, TRUE)
    AND COALESCE(:email RLIKE email, TRUE)
    AND COALESCE(:useragent RLIKE useragent, TRUE)
    AND CASE
        WHEN LENGTH(b.ip) = 4 THEN
          (CONV(HEX(b.ip), 16, 10) & n.maskl) = (CONV(HEX(INET6_ATON(:ip4)), 16, 10) & n.maskl)
        WHEN LENGTH(b.ip) = 16 THEN
            (CONV(LEFT(HEX(b.ip), 16), 16, 10) & n.maskh) = (CONV(LEFT(HEX(INET6_ATON(:ip6h)), 16), 16, 10) & n.maskh)
            AND (CONV(RIGHT(HEX(b.ip), 16), 16, 10) & n.maskl) = (CONV(RIGHT(HEX(INET6_ATON(:ip6l)), 16), 16, 10) & n.maskl)
        WHEN LENGTH(b.ip) IS NULL THEN TRUE
    END
    AND active = 1
    AND (duration > UNIX_TIMESTAMP() OR duration IS NULL)
    AND (b.domain IS NULL OR b.domain = :domain)
SQL;

        $statement = $this->database->prepare($query);
        $trustedIp = $this->xffTrustProvider->getTrustedClientIp($request->getIp(), $request->getForwardedIp());

        $statement->execute([
            ':name'      => $request->getName(),
            ':email'     => $request->getEmail(),
            ':useragent' => $request->getUserAgent(),
            ':domain'    => $request->getDomain(),
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

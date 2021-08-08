<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Statistics;

use PDO;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Log;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\LogHelper;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\IdentificationVerifier;
use Waca\Pages\PageUserManagement;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class StatsUsers extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Users :: Statistics');

        $database = $this->getDatabase();

        $query = <<<SQL
SELECT
    u.id
    , u.username
    , CASE WHEN ru.role IS NOT NULL THEN 'Yes' ELSE 'No' END tooluser
    , CASE WHEN ra.role IS NOT NULL THEN 'Yes' ELSE 'No' END tooladmin
    , CASE WHEN rc.role IS NOT NULL THEN 'Yes' ELSE 'No' END checkuser
    , CASE WHEN rr.role IS NOT NULL THEN 'Yes' ELSE 'No' END toolroot
FROM user u
    LEFT JOIN userrole ru ON ru.user = u.id AND ru.role = 'user'
    LEFT JOIN userrole ra ON ra.user = u.id AND ra.role = 'admin'
    LEFT JOIN userrole rc ON rc.user = u.id AND rc.role = 'checkuser'
    LEFT JOIN userrole rr ON rr.user = u.id AND rr.role = 'toolRoot'
WHERE u.status = 'Active'
SQL;

        $users = $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
        $this->assign('users', $users);

        $this->assign('statsPageTitle', 'Account Creation Tool users');
        $this->setTemplate("statistics/users.tpl");
    }

    /**
     * Entry point for the detail action.
     *
     * @throws ApplicationLogicException
     */
    protected function detail()
    {
        $userId = WebRequest::getInt('user');
        if ($userId === null) {
            throw new ApplicationLogicException("User not found");
        }

        $database = $this->getDatabase();

        $user = User::getById($userId, $database);
        if ($user == false) {
            throw new ApplicationLogicException('User not found');
        }


        $activitySummary = $database->prepare(<<<SQL
SELECT COALESCE(closes.mail_desc, log.action) AS action, COUNT(*) AS count
FROM log
INNER JOIN user ON log.user = user.id
LEFT JOIN closes ON log.action = closes.closes
WHERE user.username = :username
GROUP BY action;
SQL
        );
        $activitySummary->execute(array(":username" => $user->getUsername()));
        $activitySummaryData = $activitySummary->fetchAll(PDO::FETCH_ASSOC);

        $this->assign("user", $user);
        $this->assign("activity", $activitySummaryData);

        $usersCreatedQuery = $database->prepare(<<<SQL
SELECT log.timestamp time, request.name name, request.id id
FROM log
INNER JOIN request ON (request.id = log.objectid AND log.objecttype = 'Request')
INNER JOIN user ON log.user = user.id
LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
WHERE user.username = :username
    AND log.action LIKE 'Closed %'
    AND (emailtemplate.defaultaction = :created OR log.action = 'Closed custom-y')
ORDER BY log.timestamp;
SQL
        );
        $usersCreatedQuery->execute(array(":username" => $user->getUsername(), ':created' => EmailTemplate::CREATED));
        $usersCreated = $usersCreatedQuery->fetchAll(PDO::FETCH_ASSOC);
        $this->assign("created", $usersCreated);

        $usersNotCreatedQuery = $database->prepare(<<<SQL
SELECT log.timestamp time, request.name name, request.id id
FROM log
JOIN request ON request.id = log.objectid AND log.objecttype = 'Request'
JOIN user ON log.user = user.id
LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
WHERE user.username = :username
    AND log.action LIKE 'Closed %'
    AND (emailtemplate.defaultaction = :created OR log.action = 'Closed custom-n' OR log.action = 'Closed 0')
ORDER BY log.timestamp;
SQL
        );
        $usersNotCreatedQuery->execute(array(":username" => $user->getUsername(), ':created' => EmailTemplate::NOT_CREATED));
        $usersNotCreated = $usersNotCreatedQuery->fetchAll(PDO::FETCH_ASSOC);
        $this->assign("notcreated", $usersNotCreated);

        /** @var Log[] $logs */
        $logs = LogSearchHelper::get($database)
            ->byObjectType('User')
            ->byObjectId($user->getId())
            ->getRecordCount($logCount)
            ->fetch();

        if ($logCount === 0) {
            $this->assign('accountlog', array());
        }
        else {
            list($users, $logData) = LogHelper::prepareLogsForTemplate($logs, $database, $this->getSiteConfiguration());

            $this->assign("accountlog", $logData);
            $this->assign("users", $users);
        }

        $currentUser = User::getCurrent($database);
        $this->assign('canApprove', $this->barrierTest('approve', $currentUser, PageUserManagement::class));
        $this->assign('canDecline', $this->barrierTest('decline', $currentUser, PageUserManagement::class));
        $this->assign('canRename', $this->barrierTest('rename', $currentUser, PageUserManagement::class));
        $this->assign('canEditUser', $this->barrierTest('editUser', $currentUser, PageUserManagement::class));
        $this->assign('canSuspend', $this->barrierTest('suspend', $currentUser, PageUserManagement::class));
        $this->assign('canEditRoles', $this->barrierTest('editRoles', $currentUser, PageUserManagement::class));

        $oauth = new OAuthUserHelper($user, $database, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());
        $this->assign('oauth', $oauth);

        if ($user->getForceIdentified() === null) {
            $idVerifier = new IdentificationVerifier($this->getHttpHelper(), $this->getSiteConfiguration(), $this->getDatabase());
            $this->assign('identificationStatus', $idVerifier->isUserIdentified($user->getOnWikiName()) ? 'detected' : 'missing');
        }
        else {
            $this->assign('identificationStatus', $user->getForceIdentified() == 1 ? 'forced-on' : 'forced-off');
        }

        if ($oauth->isFullyLinked()) {
            $this->assign('identity', $oauth->getIdentity(true));
            $this->assign('identityExpired', $oauth->identityExpired());
        }

        $this->assign('statsPageTitle', 'Account Creation Tool users');

        $this->setHtmlTitle('{$user->getUsername()|escape} :: Users :: Statistics');
        $this->setTemplate("statistics/userdetail.tpl");
    }
}

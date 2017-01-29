<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Statistics;

use PDO;
use Waca\DataObjects\Log;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\LogHelper;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Pages\PageUserManagement;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class StatsUsers extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Users :: Statistics');

        $database = $this->getDatabase();

        $lists = array(
            "Admin"      => UserSearchHelper::get($database)->byRole('admin')->byStatus(User::STATUS_ACTIVE)->fetch(),
            "User"       => UserSearchHelper::get($database)->byStatus(User::STATUS_ACTIVE)->fetch(),
            "CheckUsers" => UserSearchHelper::get($database)->byRole('checkuser')->byStatus(User::STATUS_ACTIVE)->fetch(),
        );

        $this->assign("lists", $lists);

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

        $safeUsername = htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8');
        $this->setHtmlTitle($safeUsername . ' :: Users :: Statistics');

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
    AND (emailtemplate.oncreated = '1' OR log.action = 'Closed custom-y')
ORDER BY log.timestamp;
SQL
        );
        $usersCreatedQuery->execute(array(":username" => $user->getUsername()));
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
    AND (emailtemplate.oncreated = '0' OR log.action = 'Closed custom-n' OR log.action = 'Closed 0')
ORDER BY log.timestamp;
SQL
        );
        $usersNotCreatedQuery->execute(array(":username" => $user->getUsername()));
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

        $this->assign('statsPageTitle', 'Account Creation Tool users');
        $this->setTemplate("statistics/userdetail.tpl");
    }
}

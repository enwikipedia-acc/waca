<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\Statistics;

use DateTime;
use PDO;
use Waca\DataObjects\User;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Pages\PageUserManagement;
use Waca\Tasks\InternalPageBase;

class StatsInactiveUsers extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Inactive Users :: Statistics');

        $date = new DateTime();
        $date->modify("-90 days");

        $inactiveUsers = UserSearchHelper::get($this->getDatabase())
            ->byStatus('Active')
            ->lastActiveBefore($date)
            ->getRoleMap($roleMap)
            ->fetch();

        $this->assign('inactiveUsers', $inactiveUsers);
        $this->assign('roles', $roleMap);
        $this->assign('canDeactivate',
            $this->barrierTest('deactivate', User::getCurrent($this->getDatabase()), PageUserManagement::class));

        $immuneUsers = $this->getDatabase()
            ->query("SELECT user FROM userrole WHERE role IN ('toolRoot', 'checkuser', 'steward') GROUP BY user;")
            ->fetchAll(PDO::FETCH_COLUMN);
        
        $this->assign('immune', array_fill_keys($immuneUsers, true));

        $this->setTemplate('statistics/inactive-users.tpl');
        $this->assign('statsPageTitle', 'Inactive tool users');
    }
}

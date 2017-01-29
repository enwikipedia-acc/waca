<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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
        $date->modify("-45 days");

        $inactiveUsers = UserSearchHelper::get($this->getDatabase())
            ->byStatus('Active')
            ->lastActiveBefore($date)
            ->getRoleMap($roleMap)
            ->fetch();

        $this->assign('inactiveUsers', $inactiveUsers);
        $this->assign('roles', $roleMap);
        $this->assign('canSuspend',
            $this->barrierTest('suspend', User::getCurrent($this->getDatabase()), PageUserManagement::class));

        $immuneUsers = $this->getDatabase()
            ->query("SELECT user FROM userrole WHERE role IN ('toolRoot', 'checkuser') GROUP BY user;")
            ->fetchAll(PDO::FETCH_COLUMN);
        
        $this->assign('immune', array_fill_keys($immuneUsers, true));

        $this->setTemplate('statistics/inactive-users.tpl');
        $this->assign('statsPageTitle', 'Inactive tool users');
    }
}

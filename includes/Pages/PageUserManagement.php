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
use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\DataObjects\UserRole;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Helpers\Logger;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\PreferenceManager;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

/**
 * Class PageUserManagement
 * @package Waca\Pages
 */
class PageUserManagement extends InternalPageBase
{
    // FIXME: domains
    /** @var string */
    private $adminMailingList = 'enwiki-acc-admins@googlegroups.com';

    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main()
    {
        $this->setHtmlTitle('User Management');

        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $userSearchRequest = WebRequest::getString('usersearch');
        if ($userSearchRequest !== null) {
            $searchedUser = User::getByUsername($userSearchRequest, $database);
            if ($searchedUser !== false) {
                $this->redirect('statistics/users', 'detail', ['user' => $searchedUser->getId()]);
                return;
            }
        }

        // A bit hacky, but it's better than my last solution of creating an object for each user and passing that to
        // the template. I still don't have a particularly good way of handling this.
        OAuthUserHelper::prepareTokenCountStatement($database);

        if (WebRequest::getBoolean("showAll")) {
            $this->assign("showAll", true);

            $suspendedUsers = UserSearchHelper::get($database)->byStatus(User::STATUS_SUSPENDED)->fetch();
            $this->assign("suspendedUsers", $suspendedUsers);

            $declinedUsers = UserSearchHelper::get($database)->byStatus(User::STATUS_DECLINED)->fetch();
            $this->assign("declinedUsers", $declinedUsers);

            UserSearchHelper::get($database)->getRoleMap($roleMap);
        }
        else {
            $this->assign("showAll", false);
            $this->assign("suspendedUsers", array());
            $this->assign("declinedUsers", array());

            UserSearchHelper::get($database)->statusIn(array('New', 'Active'))->getRoleMap($roleMap);
        }

        $newUsers = UserSearchHelper::get($database)->byStatus(User::STATUS_NEW)->fetch();
        $normalUsers = UserSearchHelper::get($database)->byStatus(User::STATUS_ACTIVE)->byRole('user')->fetch();
        $adminUsers = UserSearchHelper::get($database)->byStatus(User::STATUS_ACTIVE)->byRole('admin')->fetch();
        $checkUsers = UserSearchHelper::get($database)->byStatus(User::STATUS_ACTIVE)->byRole('checkuser')->fetch();
        $stewards = UserSearchHelper::get($database)->byStatus(User::STATUS_ACTIVE)->byRole('steward')->fetch();
        $toolRoots = UserSearchHelper::get($database)->byStatus(User::STATUS_ACTIVE)->byRole('toolRoot')->fetch();
        $this->assign('newUsers', $newUsers);
        $this->assign('normalUsers', $normalUsers);
        $this->assign('adminUsers', $adminUsers);
        $this->assign('checkUsers', $checkUsers);
        $this->assign('stewards', $stewards);
        $this->assign('toolRoots', $toolRoots);

        $this->assign('roles', $roleMap);

        $this->addJs("/api.php?action=users&all=true&targetVariable=typeaheaddata");

        $this->assign('canApprove', $this->barrierTest('approve', $currentUser));
        $this->assign('canDecline', $this->barrierTest('decline', $currentUser));
        $this->assign('canRename', $this->barrierTest('rename', $currentUser));
        $this->assign('canEditUser', $this->barrierTest('editUser', $currentUser));
        $this->assign('canSuspend', $this->barrierTest('suspend', $currentUser));
        $this->assign('canEditRoles', $this->barrierTest('editRoles', $currentUser));

        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $this->getDatabase());
        $this->assign('mediawikiScriptPath', $domain->getWikiArticlePath());

        $this->setTemplate("usermanagement/main.tpl");
    }

    #region Access control

    /**
     * Action target for editing the roles assigned to a user
     *
     * @throws ApplicationLogicException
     * @throws Smarty\Exception
     * @throws OptimisticLockFailedException
     * @throws Exception
     */
    protected function editRoles(): void
    {
        $this->setHtmlTitle('User Management');
        $database = $this->getDatabase();
        $domain = Domain::getCurrent($database);
        $userId = WebRequest::getInt('user');

        /** @var User|false $user */
        $user = User::getById($userId, $database);

        if ($user === false || $user->isCommunityUser()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to edit could not be found.');
        }

        $roleData = $this->getRoleData(UserRole::getForUser($user->getId(), $database, $domain->getId()));

        // Dual-mode action
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $reason = WebRequest::postString('reason');
            if ($reason === false || trim($reason) === '') {
                throw new ApplicationLogicException('No reason specified for roles change');
            }

            /** @var UserRole[] $delete */
            $delete = array();
            /** @var string[] $add */
            $add = array();

            /** @var UserRole[] $globalDelete */
            $globalDelete = array();
            /** @var string[] $globalAdd */
            $globalAdd = array();

            foreach ($roleData as $name => $r) {
                if ($r['allowEdit'] !== 1) {
                    // not allowed, to touch this, so ignore it
                    continue;
                }

                $newValue = WebRequest::postBoolean('role-' . $name) ? 1 : 0;
                if ($newValue !== $r['active']) {
                    if ($newValue === 0) {
                        if ($r['globalOnly']) {
                            $globalDelete[] = $r['object'];
                        }
                        else {
                            $delete[] = $r['object'];
                        }
                    }

                    if ($newValue === 1) {
                        if ($r['globalOnly']) {
                            $globalAdd[] = $name;
                        }
                        else {
                            $add[] = $name;
                        }
                    }
                }
            }

            // Check there's something to do
            if ((count($add) + count($delete) + count($globalAdd) + count($globalDelete)) === 0) {
                $this->redirect('statistics/users', 'detail', array('user' => $user->getId()));
                SessionAlert::warning('No changes made to roles.');

                return;
            }

            $removed = array();
            $globalRemoved = array();

            foreach ($delete as $d) {
                $removed[] = $d->getRole();
                $d->delete();
            }

            foreach ($globalDelete as $d) {
                $globalRemoved[] = $d->getRole();
                $d->delete();
            }

            foreach ($add as $x) {
                $a = new UserRole();
                $a->setUser($user->getId());
                $a->setRole($x);
                $a->setDomain($domain->getId());
                $a->setDatabase($database);
                $a->save();
            }

            foreach ($globalAdd as $x) {
                $a = new UserRole();
                $a->setUser($user->getId());
                $a->setRole($x);
                $a->setDomain(null);
                $a->setDatabase($database);
                $a->save();
            }

            if ((count($add) + count($delete)) > 0) {
                Logger::userRolesEdited($database, $user, $reason, $add, $removed, $domain->getId());
            }

            if ((count($globalAdd) + count($globalDelete)) > 0) {
                Logger::userGlobalRolesEdited($database, $user, $reason, $globalAdd, $globalRemoved);
            }

            // dummy save for optimistic locking. If this fails, the entire txn will roll back.
            $user->setUpdateVersion(WebRequest::postInt('updateversion'));
            $user->save();

            $this->getNotificationHelper()->userRolesEdited($user, $reason);
            SessionAlert::quick('Roles changed for user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

            $this->redirect('statistics/users', 'detail', array('user' => $user->getId()));
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate('usermanagement/roleedit.tpl');
            $this->assign('user', $user);
            $this->assign('roleData', $roleData);
        }
    }

    /**
     * Action target for suspending users
     *
     * @throws ApplicationLogicException
     */
    protected function suspend()
    {
        $this->setHtmlTitle('User Management');

        $database = $this->getDatabase();

        $userId = WebRequest::getInt('user');

        /** @var User $user */
        $user = User::getById($userId, $database);

        if ($user === false || $user->isCommunityUser()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to suspend could not be found.');
        }

        if ($user->isSuspended()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to suspend is already suspended.');
        }

        // Dual-mode action
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $reason = WebRequest::postString('reason');

            if ($reason === null || trim($reason) === "") {
                throw new ApplicationLogicException('No reason provided');
            }

            $user->setStatus(User::STATUS_SUSPENDED);
            $user->setUpdateVersion(WebRequest::postInt('updateversion'));
            $user->save();
            Logger::suspendedUser($database, $user, $reason);

            $this->getNotificationHelper()->userSuspended($user, $reason);
            SessionAlert::quick('Suspended user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

            // send email
            $this->sendStatusChangeEmail(
                'Your WP:ACC account has been suspended',
                'usermanagement/emails/suspended.tpl',
                $reason,
                $user,
                User::getCurrent($database)->getUsername()
            );

            $this->redirect('userManagement');

            return;
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate('usermanagement/changelevel-reason.tpl');
            $this->assign('user', $user);
            $this->assign('status', 'Suspended');
            $this->assign("showReason", true);

            if (WebRequest::getString('preload')) {
                $this->assign('preload', WebRequest::getString('preload'));
            }
        }
    }

    /**
     * Entry point for the decline action
     *
     * @throws ApplicationLogicException
     */
    protected function decline()
    {
        $this->setHtmlTitle('User Management');

        $database = $this->getDatabase();

        $userId = WebRequest::getInt('user');
        $user = User::getById($userId, $database);

        if ($user === false || $user->isCommunityUser()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to decline could not be found.');
        }

        if (!$user->isNewUser()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to decline is not new.');
        }

        // Dual-mode action
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $reason = WebRequest::postString('reason');

            if ($reason === null || trim($reason) === "") {
                throw new ApplicationLogicException('No reason provided');
            }

            $user->setStatus(User::STATUS_DECLINED);
            $user->setUpdateVersion(WebRequest::postInt('updateversion'));
            $user->save();
            Logger::declinedUser($database, $user, $reason);

            $this->getNotificationHelper()->userDeclined($user, $reason);
            SessionAlert::quick('Declined user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

            // send email
            $this->sendStatusChangeEmail(
                'Your WP:ACC account has been declined',
                'usermanagement/emails/declined.tpl',
                $reason,
                $user,
                User::getCurrent($database)->getUsername()
            );

            $this->redirect('userManagement');

            return;
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate('usermanagement/changelevel-reason.tpl');
            $this->assign('user', $user);
            $this->assign('status', 'Declined');
            $this->assign("showReason", true);
        }
    }

    /**
     * Entry point for the approve action
     *
     * @throws ApplicationLogicException
     */
    protected function approve()
    {
        $this->setHtmlTitle('User Management');

        $database = $this->getDatabase();

        $userId = WebRequest::getInt('user');
        $user = User::getById($userId, $database);

        if ($user === false || $user->isCommunityUser()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to approve could not be found.');
        }

        if ($user->isActive()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to approve is already an active user.');
        }

        // Dual-mode action
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setUpdateVersion(WebRequest::postInt('updateversion'));
            $user->save();
            Logger::approvedUser($database, $user);

            $this->getNotificationHelper()->userApproved($user);
            SessionAlert::quick('Approved user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

            // send email
            $this->sendStatusChangeEmail(
                'Your WP:ACC account has been approved',
                'usermanagement/emails/approved.tpl',
                null,
                $user,
                User::getCurrent($database)->getUsername()
            );

            $this->redirect("userManagement");

            return;
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate("usermanagement/changelevel-reason.tpl");
            $this->assign("user", $user);
            $this->assign("status", "Active");
            $this->assign("showReason", false);
        }
    }

    #endregion

    #region Renaming / Editing

    /**
     * Entry point for the rename action
     *
     * @throws ApplicationLogicException
     */
    protected function rename()
    {
        $this->setHtmlTitle('User Management');

        $database = $this->getDatabase();

        $userId = WebRequest::getInt('user');
        $user = User::getById($userId, $database);

        if ($user === false || $user->isCommunityUser()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to rename could not be found.');
        }

        // Dual-mode action
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $newUsername = WebRequest::postString('newname');

            if ($newUsername === null || trim($newUsername) === "") {
                throw new ApplicationLogicException('The new username cannot be empty');
            }

            if (User::getByUsername($newUsername, $database) != false) {
                throw new ApplicationLogicException('The new username already exists');
            }

            $oldUsername = $user->getUsername();
            $user->setUsername($newUsername);
            $user->setUpdateVersion(WebRequest::postInt('updateversion'));

            $user->save();

            $logEntryData = serialize(array(
                'old' => $oldUsername,
                'new' => $newUsername,
            ));

            Logger::renamedUser($database, $user, $logEntryData);

            SessionAlert::quick("Changed User "
                . htmlentities($oldUsername, ENT_COMPAT, 'UTF-8')
                . " name to "
                . htmlentities($newUsername, ENT_COMPAT, 'UTF-8'));

            $this->getNotificationHelper()->userRenamed($user, $oldUsername);

            // send an email to the user.
            $this->assign('targetUsername', $user->getUsername());
            $this->assign('toolAdmin', User::getCurrent($database)->getUsername());
            $this->assign('oldUsername', $oldUsername);
            $this->assign('mailingList', $this->adminMailingList);

            // FIXME: domains!
            /** @var Domain $domain */
            $domain = Domain::getById(1, $database);
            $this->getEmailHelper()->sendMail(
                $this->adminMailingList,
                $user->getEmail(),
                'Your username on WP:ACC has been changed',
                $this->fetchTemplate('usermanagement/emails/renamed.tpl')
            );

            $this->redirect("userManagement");

            return;
        }
        else {
            $this->assignCSRFToken();
            $this->setTemplate('usermanagement/renameuser.tpl');
            $this->assign('user', $user);
        }
    }

    /**
     * Entry point for the edit action
     *
     * @throws ApplicationLogicException
     */
    protected function editUser()
    {
        $this->setHtmlTitle('User Management');

        $database = $this->getDatabase();

        $userId = WebRequest::getInt('user');
        $user = User::getById($userId, $database);
        $oauth = new OAuthUserHelper($user, $database, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());

        if ($user === false || $user->isCommunityUser()) {
            throw new ApplicationLogicException('Sorry, the user you are trying to edit could not be found.');
        }

        // FIXME: domains
        $prefs = new PreferenceManager($database, $user->getId(), 1);

        // Dual-mode action
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();
            $newEmail = WebRequest::postEmail('user_email');
            $newOnWikiName = WebRequest::postString('user_onwikiname');

            if ($newEmail === null) {
                throw new ApplicationLogicException('Invalid email address');
            }

            if ($this->validateUnusedEmail($newEmail, $userId)) {
                throw new ApplicationLogicException('The specified email address is already in use.');
            }

            if (!($oauth->isFullyLinked() || $oauth->isPartiallyLinked())) {
                if (trim($newOnWikiName) == "") {
                    throw new ApplicationLogicException('New on-wiki username cannot be blank');
                }

                $user->setOnWikiName($newOnWikiName);
            }

            $user->setEmail($newEmail);

            $prefs->setLocalPreference(PreferenceManager::PREF_CREATION_MODE, WebRequest::postInt('creationmode'));

            $user->setUpdateVersion(WebRequest::postInt('updateversion'));

            $user->save();

            Logger::userPreferencesChange($database, $user);
            $this->getNotificationHelper()->userPrefChange($user);
            SessionAlert::quick('Changes to user\'s preferences have been saved');

            $this->redirect("userManagement");

            return;
        }
        else {
            $this->assignCSRFToken();
            $oauth = new OAuthUserHelper($user, $database, $this->getOAuthProtocolHelper(),
                $this->getSiteConfiguration());
            $this->setTemplate('usermanagement/edituser.tpl');
            $this->assign('user', $user);
            $this->assign('oauth', $oauth);

            $this->assign('preferredCreationMode', (int)$prefs->getPreference(PreferenceManager::PREF_CREATION_MODE));
            $this->assign('emailSignature', $prefs->getPreference(PreferenceManager::PREF_EMAIL_SIGNATURE));

            $this->assign('canManualCreate',
                $this->barrierTest(PreferenceManager::CREATION_MANUAL, $user, 'RequestCreation'));
            $this->assign('canOauthCreate',
                $this->barrierTest(PreferenceManager::CREATION_OAUTH, $user, 'RequestCreation'));
            $this->assign('canBotCreate',
                $this->barrierTest(PreferenceManager::CREATION_BOT, $user, 'RequestCreation'));
        }
    }

    #endregion

    private function validateUnusedEmail(string $email, int $userId) : bool {
        $query = 'SELECT COUNT(id) FROM user WHERE email = :email AND id <> :uid';
        $statement = $this->getDatabase()->prepare($query);
        $statement->execute(array(':email' => $email, ':uid' => $userId));
        $inUse = $statement->fetchColumn() > 0;
        $statement->closeCursor();

        return $inUse;
    }

    /**
     * Sends a status change email to the user.
     *
     * @param string      $subject           The subject of the email
     * @param string      $template          The smarty template to use
     * @param string|null $reason            The reason for performing the status change
     * @param User        $user              The user affected
     * @param string      $toolAdminUsername The tool admin's username who is making the edit
     */
    private function sendStatusChangeEmail($subject, $template, $reason, $user, $toolAdminUsername)
    {
        $this->assign('targetUsername', $user->getUsername());
        $this->assign('toolAdmin', $toolAdminUsername);
        $this->assign('actionReason', $reason);
        $this->assign('mailingList', $this->adminMailingList);

        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $this->getDatabase());
        $this->getEmailHelper()->sendMail(
            $this->adminMailingList,
            $user->getEmail(),
            $subject,
            $this->fetchTemplate($template)
        );
    }

    /**
     * @param UserRole[] $activeRoles
     *
     * @return array
     */
    private function getRoleData($activeRoles)
    {
        $availableRoles = $this->getSecurityManager()->getRoleConfiguration()->getAvailableRoles();

        $currentUser = User::getCurrent($this->getDatabase());
        $this->getSecurityManager()->getActiveRoles($currentUser, $userRoles, $inactiveRoles);

        $initialValue = array('active' => 0, 'allowEdit' => 0, 'description' => '???', 'object' => null);

        $roleData = array();
        foreach ($availableRoles as $role => $data) {
            $intersection = array_intersect($data['editableBy'], $userRoles);

            $roleData[$role] = $initialValue;
            $roleData[$role]['allowEdit'] = count($intersection) > 0 ? 1 : 0;
            $roleData[$role]['description'] = $data['description'];
            $roleData[$role]['globalOnly'] = $data['globalOnly'];
        }

        foreach ($activeRoles as $role) {
            if (!isset($roleData[$role->getRole()])) {
                // This value is no longer available in the configuration, allow changing (aka removing) it.
                $roleData[$role->getRole()] = $initialValue;
                $roleData[$role->getRole()]['allowEdit'] = 1;
            }

            $roleData[$role->getRole()]['object'] = $role;
            $roleData[$role->getRole()]['active'] = 1;
        }

        return $roleData;
    }
}

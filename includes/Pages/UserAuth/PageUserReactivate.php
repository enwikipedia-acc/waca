<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use Exception;
use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Fragments\LogEntryLookup;
use Waca\Helpers\Logger;
use Waca\Helpers\PreferenceManager;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageUserReactivate extends InternalPageBase
{
    use LogEntryLookup;

    /**
     * @throws ApplicationLogicException
     * @throws Exception
     */
    protected function main()
    {
        $db = $this->getDatabase();
        $currentUser = User::getCurrent($db);

        // *Only* deactivated users should be able to access this.
        // Redirect anyone else away.
        if (!$currentUser->isDeactivated()) {
            $this->redirect();
            return;
        }

        $ableToAppeal = true;
        $prefs = new PreferenceManager($db, $currentUser->getId(), Domain::getCurrent($db)->getId());
        if ($prefs->getPreference(PreferenceManager::ADMIN_PREF_PREVENT_REACTIVATION) ?? false) {
            $ableToAppeal = false;
        }

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $reason = WebRequest::postString('reason');
            $updateVersion = WebRequest::postInt('updateVersion');

            if (!$ableToAppeal) {
                throw new ApplicationLogicException('Appeal is disabled');
            }

            if ($reason === null || trim($reason) === '') {
                throw new ApplicationLogicException('The reason field cannot be empty.');
            }

            Logger::requestedReactivation($db, $currentUser, $reason);
            $currentUser->setStatus(User::STATUS_NEW);
            $currentUser->setUpdateVersion($updateVersion);
            $currentUser->save();

            SessionAlert::success('Reactivation request has been saved. Please wait for a response from the tool admin team.');
            $this->redirect();
        }
        else {
            $this->assignCSRFToken();
            $this->assign('deactivationReason', $this->getLogEntry('DeactivatedUser', $currentUser, $db));
            $this->assign('updateVersion', $currentUser->getUpdateVersion());
            $this->assign('ableToAppeal', $ableToAppeal);
            $this->setTemplate('reactivate.tpl');
        }
    }
}
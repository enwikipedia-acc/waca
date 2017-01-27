<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Registration;

use Waca\DataObjects\User;
use Waca\DataObjects\UserRole;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

abstract class PageRegisterBase extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main()
    {
        $useOAuthSignup = $this->getSiteConfiguration()->getUseOAuthSignup();

        // Dual-mode page
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            try {
                $this->handlePost($useOAuthSignup);
            }
            catch (ApplicationLogicException $ex) {
                SessionAlert::error($ex->getMessage());
                $this->redirect('register');
            }
        }
        else {
            $this->assignCSRFToken();
            $this->assign("useOAuthSignup", $useOAuthSignup);
            $this->setTemplate($this->getRegistrationTemplate());
        }
    }

    protected abstract function getRegistrationTemplate();

    protected function isProtectedPage()
    {
        return false;
    }

    /**
     * @param string $emailAddress
     *
     * @throws ApplicationLogicException
     */
    protected function validateUniqueEmail($emailAddress)
    {
        $query = 'SELECT COUNT(id) FROM user WHERE email = :email';
        $statement = $this->getDatabase()->prepare($query);
        $statement->execute(array(':email' => $emailAddress));

        if ($statement->fetchColumn() > 0) {
            throw new ApplicationLogicException('That email address is already in use on this system.');
        }

        $statement->closeCursor();
    }

    /**
     * @param $emailAddress
     * @param $password
     * @param $username
     * @param $useOAuthSignup
     * @param $confirmationId
     * @param $onwikiUsername
     *
     * @throws ApplicationLogicException
     */
    protected function validateRequest(
        $emailAddress,
        $password,
        $username,
        $useOAuthSignup,
        $confirmationId,
        $onwikiUsername
    ) {
        if (!WebRequest::postBoolean('guidelines')) {
            throw new ApplicationLogicException('You must read the interface guidelines before your request may be submitted.');
        }

        $this->validateGeneralInformation($emailAddress, $password, $username);
        $this->validateUniqueEmail($emailAddress);
        $this->validateNonOAuthFields($useOAuthSignup, $confirmationId, $onwikiUsername);
    }

    /**
     * @param $useOAuthSignup
     * @param $confirmationId
     * @param $onwikiUsername
     *
     * @throws ApplicationLogicException
     */
    protected function validateNonOAuthFields($useOAuthSignup, $confirmationId, $onwikiUsername)
    {
        if (!$useOAuthSignup) {
            if ($confirmationId === null || $confirmationId <= 0) {
                throw new ApplicationLogicException('Please enter the revision id of your confirmation edit.');
            }

            if ($onwikiUsername === null) {
                throw new ApplicationLogicException('Please specify your on-wiki username.');
            }
        }
    }

    /**
     * @param $emailAddress
     * @param $password
     * @param $username
     *
     * @throws ApplicationLogicException
     */
    protected function validateGeneralInformation($emailAddress, $password, $username)
    {
        if ($emailAddress === null) {
            throw new ApplicationLogicException('Your email address appears to be invalid!');
        }

        if ($password !== WebRequest::postString('pass2')) {
            throw new ApplicationLogicException('Your passwords did not match, please try again.');
        }

        if (User::getByUsername($username, $this->getDatabase()) !== false) {
            throw new ApplicationLogicException('That username is already in use on this system.');
        }
    }

    /**
     * @param $useOAuthSignup
     *
     * @throws ApplicationLogicException
     * @throws \Exception
     */
    protected function handlePost($useOAuthSignup)
    {
        // Get the data
        $emailAddress = WebRequest::postEmail('email');
        $password = WebRequest::postString('pass');
        $username = WebRequest::postString('name');

        // Only set if OAuth is disabled
        $confirmationId = WebRequest::postInt('conf_revid');
        $onwikiUsername = WebRequest::postString('wname');

        // Do some validation
        $this->validateRequest($emailAddress, $password, $username, $useOAuthSignup, $confirmationId,
            $onwikiUsername);

        $database = $this->getDatabase();

        $user = new User();
        $user->setDatabase($database);

        $user->setUsername($username);
        $user->setPassword($password);
        $user->setEmail($emailAddress);

        if (!$useOAuthSignup) {
            $user->setOnWikiName($onwikiUsername);
            $user->setConfirmationDiff($confirmationId);
        }

        $user->save();

        $defaultRole = $this->getDefaultRole();

        $role = new UserRole();
        $role->setDatabase($database);
        $role->setUser($user->getId());
        $role->setRole($defaultRole);
        $role->save();

        // Log now to get the signup date.
        Logger::newUser($database, $user);
        Logger::userRolesEdited($database, $user, 'Registration', array($defaultRole), array());

        if ($useOAuthSignup) {
            $oauthHelper = $this->getOAuthHelper();

            $requestToken = $oauthHelper->getRequestToken();
            $user->setOAuthRequestToken($requestToken->key);
            $user->setOAuthRequestSecret($requestToken->secret);
            $user->save();

            WebRequest::setPartialLogin($user);

            $this->redirectUrl($oauthHelper->getAuthoriseUrl($requestToken->key));
        }
        else {
            // only notify if we're not using the oauth signup.
            $this->getNotificationHelper()->userNew($user);
            WebRequest::setLoggedInUser($user);
            $this->redirect('preferences');
        }
    }

    protected abstract function getDefaultRole();

    /**
     * Entry point for registration complete
     */
    protected function done()
    {
        $this->setTemplate('registration/alert-registrationcomplete.tpl');
    }
}

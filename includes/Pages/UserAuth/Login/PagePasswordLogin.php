<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\UserAuth\Login;

use Waca\Exceptions\ApplicationLogicException;
use Waca\WebRequest;

class PagePasswordLogin extends LoginCredentialPageBase
{
    protected function providerSpecificSetup()
    {
        list($partialId, $partialStage) = WebRequest::getAuthPartialLogin();

        if ($partialId !== null && $partialStage > 1) {
            $sql = 'SELECT type FROM credential WHERE user = :user AND factor = :stage AND disabled = 0 ORDER BY priority';
            $statement = $this->getDatabase()->prepare($sql);
            $statement->execute(array(':user' => $partialId, ':stage' => $partialStage));
            $nextStage = $statement->fetchColumn();
            $statement->closeCursor();

            $this->redirect("login/" . $this->nextPageMap[$nextStage]);
            return;
        }

        $this->setTemplate('login/password.tpl');
    }

    protected function getProviderCredentials()
    {
        $password = WebRequest::postString("password");
        if ($password === null || $password === "") {
            throw new ApplicationLogicException("No password specified");
        }

        return $password;
    }
}
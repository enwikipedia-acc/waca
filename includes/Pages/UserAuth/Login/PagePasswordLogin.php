<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth\Login;

use Waca\Exceptions\ApplicationLogicException;
use Waca\WebRequest;

class PagePasswordLogin extends LoginCredentialPageBase
{
    protected function providerSpecificSetup()
    {
        list($partialId, $partialStage, $partialToken) = WebRequest::getAuthPartialLogin();

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
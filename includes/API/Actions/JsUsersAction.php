<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\API\Actions;

use Waca\API\IJsonApiAction;
use Waca\DataObjects\User;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Tasks\JsonApiPageBase;
use Waca\WebRequest;

class JsUsersAction extends JsonApiPageBase implements IJsonApiAction
{
    public function executeApiAction()
    {
        $this->getDatabase();

        $userSearchHelper = UserSearchHelper::get($this->getDatabase());

        if (WebRequest::getString('all') === null) {
            $userSearchHelper->byStatus(User::STATUS_ACTIVE);

        }

        $dataset = $userSearchHelper->fetchColumn('username');
        return $dataset;
    }
}

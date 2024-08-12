<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
        $userSearchHelper = UserSearchHelper::get($this->getDatabase());

        if (WebRequest::getString('all') === null) {
            $userSearchHelper->byStatus(User::STATUS_ACTIVE);

        }

        $dataset = $userSearchHelper->fetchColumn('username');
        return $dataset;
    }
}

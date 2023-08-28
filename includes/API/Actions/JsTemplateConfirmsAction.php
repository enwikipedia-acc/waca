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
use Waca\DataObjects\EmailTemplate;
use Waca\Tasks\JsonApiPageBase;

class JsTemplateConfirmsAction extends JsonApiPageBase implements IJsonApiAction
{
    public function executeApiAction()
    {
        /** @var EmailTemplate[] $templates */
        // FIXME: domains!
        $templates = EmailTemplate::getAllActiveTemplates(null, $this->getDatabase(), 1);

        $dataset = [];
        foreach ($templates as $tpl) {
            if ($tpl->getJsquestion() != "") {
                $dataset[$tpl->getId()] = $tpl->getJsquestion();
            }
        }

        return $dataset;
    }
}

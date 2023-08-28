<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\API\Actions;

use Waca\API\IJsonApiAction;
use Waca\DataObjects\EmailTemplate;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Tasks\JsonApiPageBase;
use Waca\Tests\WebRequestTest;
use Waca\WebRequest;

class JsTemplateConfirmsAction extends JsonApiPageBase implements IJsonApiAction
{
    public function executeApiAction()
    {
        $domainId = WebRequest::getInt('d');

        if($domainId === null) {
            throw new ApplicationLogicException("Unknown domain");
        }

        /** @var EmailTemplate[] $templates */
        $templates = EmailTemplate::getAllActiveTemplates(null, $this->getDatabase(), $domainId);

        $dataset = [];
        foreach ($templates as $tpl) {
            if ($tpl->getJsquestion() != "") {
                $dataset[$tpl->getId()] = $tpl->getJsquestion();
            }
        }

        return $dataset;
    }
}

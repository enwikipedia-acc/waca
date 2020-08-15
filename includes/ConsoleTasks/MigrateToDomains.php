<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Waca\DataObjects\Domain;
use Waca\DataObjects\RequestQueue;
use Waca\Tasks\ConsoleTaskBase;

class MigrateToDomains extends ConsoleTaskBase
{
    public function execute()
    {
        $requestStates = $this->getSiteConfiguration()->getRequestStates();
        $database = $this->getDatabase();

        $domain = new Domain();
        $domain->setEnabled(true);
        $domain->setShortName('enwiki');
        $domain->setLongName('English Wikipedia');
        $domain->setWikiArticlePath($this->getSiteConfiguration()->getMediawikiScriptPath());
        $domain->setWikiApiPath($this->getSiteConfiguration()->getMediawikiWebServiceEndpoint());
        $domain->setEnabled(true);
        $domain->setDefaultClose($this->getSiteConfiguration()->getDefaultCreatedTemplateId());
        $domain->setDefaultLanguage('en');
        $domain->setEmailSender('accounts-enwiki-l@lists.wikimedia.org');
        $domain->setNotificationTarget($this->getSiteConfiguration()->getIrcNotificationType());

        $domain->setDatabase($database);
        $domain->save();

        $first = true;
        foreach ($requestStates as $key => $data) {
            $state = new RequestQueue();
            $state->setDefault($first);
            $state->setDomain($domain->getId());
            $state->setApiName($data['api']);
            $state->setDisplayName($data['deferto']);
            $state->setHeader($data['header']);
            $state->setLogName($data['defertolog']);
            $state->setLegacyStatus($key);
            $state->setEnabled(true);

            if (isset($data['help'])) {
                $state->setHelp($data['help']);
            }

            $state->setDatabase($database);
            $state->save();

            $first = false;
        }

        /** @noinspection SqlWithoutWhere */
        $database->exec("UPDATE schemaversion SET version = 37;");
    }
}

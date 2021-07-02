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
        $siteConfiguration = $this->getSiteConfiguration();

        /** @noinspection PhpDeprecationInspection */
        $requestStates = $siteConfiguration->getRequestStates();
        $database = $this->getDatabase();

        $domain = new Domain();
        $domain->setEnabled(true);
        $domain->setShortName('enwiki');
        $domain->setLongName('English Wikipedia');
        $domain->setWikiArticlePath($siteConfiguration->getMediawikiScriptPath());
        $domain->setWikiApiPath($siteConfiguration->getMediawikiWebServiceEndpoint());
        $domain->setEnabled(true);
        /** @noinspection PhpDeprecationInspection */
        $domain->setDefaultClose($siteConfiguration->getDefaultCreatedTemplateId());
        $domain->setDefaultLanguage('en');
        $domain->setEmailSender('accounts-enwiki-l@lists.wikimedia.org');
        $domain->setNotificationTarget($siteConfiguration->getIrcNotificationType());

        $domain->setDatabase($database);
        $domain->save();

        foreach ($requestStates as $key => $data) {
            $state = new RequestQueue();

            /** @noinspection PhpDeprecationInspection */
            if ($siteConfiguration->getDefaultRequestStateKey() === $key) {
                $state->setDefault(true);
            }

            /** @noinspection PhpDeprecationInspection */
            if ($siteConfiguration->getDefaultRequestDeferredStateKey() === $key) {
                $state->setDefaultAntispoof(true);
                $state->setDefaultTitleBlacklist(true);
            }

            $state->setDomain($domain->getId());
            $state->setApiName($data['api']);
            $state->setDisplayName($data['deferto']);
            $state->setHeader($data['header']);
            /** @noinspection PhpDeprecationInspection */
            $state->setLogName($data['defertolog']);
            /** @noinspection PhpDeprecationInspection */
            $state->setLegacyStatus($key);
            $state->setEnabled(true);

            if (isset($data['help'])) {
                $state->setHelp($data['help']);
            }

            $state->setDatabase($database);
            $state->save();
        }

        /** @noinspection SqlWithoutWhere */
        $database->exec("UPDATE schemaversion SET version = 37;");
    }
}

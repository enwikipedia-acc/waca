<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Domain;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Helpers\Logger;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageDomainManagement extends InternalPageBase
{
    protected function main()
    {
        $this->setHtmlTitle('Domain Management');

        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        /** @var Domain[] $domains */
        $domains = Domain::getAll($database);

        $templates = [];
        foreach ($domains as $domain) {
            if ($domain->getDefaultClose() !== null) {
                $templates[$domain->getDefaultClose()] = EmailTemplate::getById($domain->getDefaultClose(), $database);
            }
        }

        $canEdit = $this->barrierTest('edit', $currentUser);
        $canEditAll = $this->barrierTest('editAll', $currentUser);
        $canCreate = $this->barrierTest('create', $currentUser);
        $this->assign('canEdit', $canEdit);
        $this->assign('canEditAll', $canEditAll);
        $this->assign('canCreate', $canCreate);

        $this->assign('domains', $domains);
        $this->assign('closeTemplates', $templates);
        $this->assign('currentDomain', Domain::getCurrent($database));
        $this->setTemplate('domain-management/main.tpl');
    }

    protected function create()
    {
        $this->setHtmlTitle('Domain Management');
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        // quickly check the user is allowed to edit all fields. If not, then they shouldn't be allowed to create
        // new domains either. With any luck, a competent developer would never grant create without editAll to a role
        // anyway, so this will never be hit.
        if (!$this->barrierTest('editAll', $currentUser)) {
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $domain = new Domain();
            $domain->setDatabase($database);

            $domain->setShortName(WebRequest::postString('shortName'));
            $domain->setLongName(WebRequest::postString('longName'));
            $domain->setWikiArticlePath(WebRequest::postString('articlePath'));
            $domain->setWikiApiPath(WebRequest::postString('apiPath'));
            $domain->setEnabled(WebRequest::postBoolean('enabled'));
            $domain->setDefaultLanguage(WebRequest::postString('defaultLanguage'));
            $domain->setDefaultClose(null);
            $domain->setEmailReplyAddress(WebRequest::postString('emailReplyTo'));
            $domain->setNotificationTarget(WebRequest::postString('notificationTarget'));
            $domain->setLocalDocumentation(WebRequest::postString('localDocumentation'));

            $domain->save();

            Logger::domainCreated($database, $domain);
            $this->redirect('domainManagement');
        }
        else {
            $this->assignCSRFToken();

            $this->assign('shortName', '');
            $this->assign('longName', '');
            $this->assign('articlePath', '');
            $this->assign('apiPath', '');
            $this->assign('enabled', false);
            $this->assign('defaultLanguage', 'en');
            $this->assign('emailReplyTo', '');
            $this->assign('notificationTarget', '');
            $this->assign('localDocumentation', '');

            $this->assign('createMode', true);
            $this->assign('canEditAll', true);

            $this->setTemplate('domain-management/edit.tpl');
        }
    }

    protected function edit()
    {
        $this->setHtmlTitle('Domain Management');
        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        $canEditAll = $this->barrierTest('editAll', $currentUser);

        /** @var Domain $domain */
        $domain = Domain::getById(WebRequest::getInt('domain'), $database);

        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $domain->setLongName(WebRequest::postString('longName'));
            $domain->setDefaultLanguage(WebRequest::postString('defaultLanguage'));
            $domain->setLocalDocumentation(WebRequest::postString('localDocumentation'));

            /** @var EmailTemplate $template */
            $template = EmailTemplate::getById(WebRequest::postInt('defaultClose'), $database);
            if ($template->getActive()
                && $template->getPreloadOnly() === false
                && $template->getDefaultAction() === EmailTemplate::ACTION_CREATED) {
                $domain->setDefaultClose(WebRequest::postInt('defaultClose'));
            }
            else {
                SessionAlert::warning("Chosen email template is not valid for use as the default creation template");
            }

            if ($canEditAll) {
                $domain->setWikiArticlePath(WebRequest::postString('articlePath'));
                $domain->setWikiApiPath(WebRequest::postString('apiPath'));
                $domain->setEnabled(WebRequest::postBoolean('enabled'));
                $domain->setEmailReplyAddress(WebRequest::postString('emailReplyTo'));
                $domain->setNotificationTarget(WebRequest::postString('notificationTarget'));
            }

            $domain->save();

            Logger::domainEdited($database, $domain);
            $this->redirect('domainManagement');
        }
        else {
            $this->assignCSRFToken();

            $templates = EmailTemplate::getActiveNonpreloadTemplates(EmailTemplate::ACTION_CREATED, $database);
            $this->assign('closeTemplates', $templates);

            $this->assign('shortName', $domain->getShortName());
            $this->assign('longName', $domain->getLongName());
            $this->assign('articlePath', $domain->getWikiArticlePath());
            $this->assign('apiPath', $domain->getWikiApiPath());
            $this->assign('enabled', $domain->isEnabled());
            $this->assign('defaultClose', $domain->getDefaultClose());
            $this->assign('defaultLanguage', $domain->getDefaultLanguage());
            $this->assign('emailReplyTo', $domain->getEmailReplyAddress());
            $this->assign('notificationTarget', $domain->getNotificationTarget());
            $this->assign('localDocumentation', $domain->getLocalDocumentation());


            $this->assign('createMode', false);
            $this->assign('canEditAll', $canEditAll);

            $this->setTemplate('domain-management/edit.tpl');
        }
    }
}

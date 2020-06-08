<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\SiteNotice;
use Waca\Helpers\Logger;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageSiteNotice extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->setHtmlTitle('Site Notice');

        $database = $this->getDatabase();

        /** @var SiteNotice $siteNoticeMessage */
        $siteNoticeMessage = SiteNotice::getById(1, $database);

        // Dual-mode
        if (WebRequest::wasPosted()) {
            $this->validateCSRFToken();

            $siteNoticeMessage->setContent(WebRequest::postString('mailtext'));
            $siteNoticeMessage->setUpdateVersion(WebRequest::postInt('updateversion'));
            $siteNoticeMessage->save();

            Logger::siteNoticeEdited($database, $siteNoticeMessage);
            $this->getNotificationHelper()->siteNoticeEdited();

            $this->redirect();
        }
        else {
            $this->assignCSRFToken();

            $this->setTemplate('site-notice/edit-form.tpl');
            $this->assign('message', $siteNoticeMessage);
        }
    }
}

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\DataObjects\Domain;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Helpers\Interfaces\IEmailHelper;

class RequestEmailHelper
{
    /**
     * @var IEmailHelper
     */
    private $emailHelper;

    /**
     * RequestEmailHelper constructor.
     *
     * @param IEmailHelper $emailHelper
     */
    public function __construct(IEmailHelper $emailHelper)
    {
        $this->emailHelper = $emailHelper;
    }

    /**
     * @param Request $request
     * @param string  $mailText
     * @param User    $sendingUser      The user sending the email
     * @param boolean $ccMailingList
     */
    public function sendMail(Request $request, $mailText, User $sendingUser, $ccMailingList)
    {
        $headers = array(
            'X-ACC-Request' => $request->getId(),
            'X-ACC-UserID'  => $sendingUser->getId(),
        );

        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $request->getDatabase());

        if ($ccMailingList) {
            $headers['Cc'] = $domain->getEmailReplyAddress();
        }

        $helper = $this->emailHelper;

        // FIXME: domains
        $preferenceManager = new PreferenceManager($request->getDatabase(), $sendingUser->getId(), 1);

        $emailSig = $preferenceManager->getPreference(PreferenceManager::PREF_EMAIL_SIGNATURE);
        if ($emailSig !== '' || $emailSig !== null) {
            $emailSig = "\n\n" . $emailSig;
        }

        $subject = "RE: [ACC #{$request->getId()}] English Wikipedia Account Request";
        $content = $mailText . $emailSig;

        $helper->sendMail($domain->getEmailReplyAddress(), $request->getEmail(), $subject, $content, $headers);

        $request->setEmailSent(true);
    }
}

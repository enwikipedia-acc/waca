<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\Helpers\Interfaces\IEmailHelper;

class EmailHelper implements IEmailHelper
{
    /** @var string */
    private $emailFrom;
    private $instance;

    public function __construct(string $emailFrom, $instance)
    {
        $this->emailFrom = $emailFrom;
        $this->instance = $instance;
    }

    /**
     * @param string|null $replyAddress
     * @param string      $to
     * @param string      $subject
     * @param string      $content
     * @param array       $headers Extra headers to include
     */
    public function sendMail(?string $replyAddress, $to, $subject, $content, $headers = array())
    {
        if ($replyAddress !== null) {
            $headers['Reply-To'] = $replyAddress;
        }

        $headers['From'] = $this->emailFrom;
        $headers['X-ACC-Instance'] = $this->instance;
        $headerString = '';

        foreach ($headers as $header => $headerValue) {
            $headerString .= $header . ': ' . $headerValue . "\r\n";
        }

        mail($to, $subject, $content, $headerString);
    }
}
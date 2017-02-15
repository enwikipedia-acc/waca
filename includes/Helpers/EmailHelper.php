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
    /**
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param array  $headers Extra headers to include
     */
    public function sendMail($to, $subject, $content, $headers = array())
    {
        $headers['From'] = 'accounts-enwiki-l@lists.wikimedia.org';
        $headerString = '';

        foreach ($headers as $header => $headerValue) {
            $headerString .= $header . ': ' . $headerValue . "\r\n";
        }

        mail($to, $subject, $content, $headerString);
    }
}
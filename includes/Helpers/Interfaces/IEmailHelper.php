<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\Interfaces;

/**
 * Interface IEmailHelper
 *
 * Encapsulates sending email
 *
 * @package Waca\Helpers\Interfaces
 */
interface IEmailHelper
{
    /**
     * Sends an email to the specified email address.
     *
     * @param string $replyAddress
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param array  $headers Extra headers to include
     *
     * @return void
     */
    public function sendMail(?string $replyAddress, $to, $subject, $content, $headers = array());
}
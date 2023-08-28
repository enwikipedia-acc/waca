<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
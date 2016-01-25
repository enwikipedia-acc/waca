<?php

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
	 * @param string $to
	 * @param string $subject
	 * @param string $content
	 * @param array  $headers Extra headers to include
	 * @return
	 */
	public function sendMail($to, $subject, $content, $headers = array());
}
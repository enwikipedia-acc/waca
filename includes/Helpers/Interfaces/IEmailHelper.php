<?php

namespace Waca\Helpers\Interfaces;

interface IEmailHelper
{
	/**
	 * @param string $to
	 * @param string $subject
	 * @param string $content
	 * @param array  $headers Extra headers to include
	 * @return
	 */
	public function sendMail($to, $subject, $content, $headers = array());
}
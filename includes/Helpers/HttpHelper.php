<?php

namespace Waca\Helpers;

class HttpHelper
{
	/**
	 * @todo Probably need to make this much, much better.
	 * @param string $url
	 * @return string
	 */
	public function get($url)
	{
		return file_get_contents($url);
	}
}
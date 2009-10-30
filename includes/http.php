<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

class http {
	private $curl;
	private $cookiejar;
	public $useragent;
	
	public function __construct () {
		$this->useragent = 'PHP cURL';
		$this->curl = curl_init();
		
		$this->cookiejar = '/tmp/http.cookies.'.dechex(rand(0,99999999)).'.dat';
		touch($this->cookiejar);
		chmod($this->cookiejar,0600);
		curl_setopt($this->curl,CURLOPT_COOKIEJAR,$this->cookiejar);
		curl_setopt($this->curl,CURLOPT_COOKIEFILE,$this->cookiejar);
	}
	
	/*
	 * Sends a GET request for $url.
	 */
	public function get ($url) {
		curl_setopt($this->curl,CURLOPT_URL,$url);
		curl_setopt($this->curl,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($this->curl,CURLOPT_MAXREDIRS,10);
		curl_setopt($this->curl,CURLOPT_HEADER,false);
		curl_setopt($this->curl,CURLOPT_HTTPGET,true);
		curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($this->curl,CURLOPT_CONNECTTIMEOUT,15);
		curl_setopt($this->curl,CURLOPT_TIMEOUT,40);
		curl_setopt($this->curl,CURLOPT_USERAGENT,$this->useragent);
		return curl_exec($this->curl);
	}
	
	/*
	 * Sends a POST request to $url.
	 * $data is the post data.
	 */
	public function post ($url,$data) {
		curl_setopt($this->curl,CURLOPT_URL,$url);
		curl_setopt($this->curl,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($this->curl,CURLOPT_MAXREDIRS,10);
		curl_setopt($this->curl,CURLOPT_HEADER,false);
		curl_setopt($this->curl,CURLOPT_POST,true);
		curl_setopt($this->ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($this->curl,CURLOPT_CONNECTTIMEOUT,15);
		curl_setopt($this->curl,CURLOPT_TIMEOUT,40);
		curl_setopt($this->curl,CURLOPT_USERAGENT,$this->useragent);
		curl_setopt($this->curl,CURLOPT_HTTPHEADER,array('Expect:'));
		return curl_exec($this->curl);
	}
	
	public function __destroy () {
		curl_close($this->curl);
		@unlink($this->cookiejar);
	}
}

?>

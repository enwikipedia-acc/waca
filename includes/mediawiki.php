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

class mediawiki {
	private $username;
	private $password;
	private $loggedin;
	private $url;
	public $http;
	
	public function __construct ($url='http://en.wikipedia.org/w/api.php',$username=null,$password=null) {
		require_once 'http.php';
		$this->http = new http();
		$this->http->useragent = 'PHP Mediawiki Client';
		
		$this->username = $username;
		$this->password = $password;
		$this->loggedin = false;
		$this->url      = $url;
		
		if ($username != null && $password != null) {
			$return = $this->login($username,$password);
		}
	}
	
	public function query ($query,$post=null) {
		if ($post==null) {
			$data = $this->http->get($this->url.$query.'&format=php');
		} else {
			$data = $this->http->post($this->url.$query.'&format=php',$post);
		}
		return unserialize($data);
	}
	
	public function login ($username,$password) {
		$this->loggedin = true;
		$post = array(
				'lgname'     => $username,
				'lgpassword' => $password
			);
		$return = $this->query('?action=login',$post);
		if ($ret['login']['result'] == 'Success') {
			return true;
		} else {
			die('Error logging in: '.$ret['login']['result']);
		}
	}
	
	public function __destruct () {
		if ($this->loggedin) {
			$this->query('?action=logout');
		}
	}
}

?>

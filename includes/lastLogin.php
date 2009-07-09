<?php

/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
**  SQL ( http://en.wikipedia.org/User:SQL )                 **
**  Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
**FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
**Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
**Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
**Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
**OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
**Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
**FunPika    ( http://en.wikipedia.org/wiki/User:FunPika )   **
**PRom3th3an ( http://en.wikipedia.org/wiki/User:Promethean )**
**Chris_G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**************************************************************/

class lastLogin {
	private $filename;
	
	public function __construct() {
		$this->filename = '/projects/acc/lastlogin.txt';
	}
	
	public function addEntry ($user) {
		$this->cleanList($user);
		$ip = $_SERVER['REMOTE_ADDR'];
		$time = time();
		file_put_contents($this->filename,"$user $ip $time\n",FILE_APPEND);
	}
	
	public function getLastLogin ($user) {
		$quser = preg_quote($user,'/');
		$data = array();
		$text = explode("\n",file_get_contents($this->filename));
		foreach ($text as $line) {
			if (preg_match("/^($quser) ([0-9.]+) (\d+)$/",$line,$m)) {
				$data[] = array(
						'ip' => $m[2],
						'time' => $m[3]
					);
			}
		}
		$a = array();
		for ($i=0;$i<count($data);$i++) {
			if (empty($a)) {
				$a = $data[$i];
			}
			if ($data[$i]['time'] < $a['time']) {
				$a = $data[$i];
			}
		}
		return $a;
	}
	
	private function cleanList ($user) {
		$newtext = '';
		$quser = preg_quote($user,'/');
		$data = array();
		$text = explode("\n",file_get_contents($this->filename));
		foreach ($text as $line) {
			if (preg_match("/^($quser) ([0-9.]+) (\d+)$/",$line,$m)) {
				$data[] = array(
						'username' => $m[1],
						'ip' => $m[2],
						'time' => $m[3]
					);
				$newtext = str_replace("$line\n",'',$text);
			}
		}
		if (count($data) < 2) {
			return true;
		}
		$a = array();
		$b = array();
		for ($i=0;$i<count($data);$i++) {
			if (empty($a)) {
				$a = $data[$i];
			}
			if (empty($b)) {
				$b = $a;
			}
			if ($data[$i]['time'] > $a['time']) {
				$b = $a;
				$a = $data[$i];
			}
		}
		$newtext .= $a['username'].' '.$a['ip'].' '.$a['time']."\n";
		$newtext .= $b['username'].' '.$b['ip'].' '.$b['time']."\n";
		file_put_contents($this->filename,$newtext);
	}
}

?>

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
**Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**************************************************************/

if ($ACC != "1") {
    header("Location: $tsurl/");
    die();
} //Re-route, if you're a web client.

// TODO: Add more fonts from http://openfontlibrary.org/

class captcha {
	public function doCaptcha () {
		$pwd = $this->generatePasswd(4);
		$this->showImage($pwd,200,60);
	}
	
	public function verifyPasswd ($passwd) {
		if ($passwd==$_SESSION['captcha']) {
			return true;
		} else {
			return false;
		}
	}
	
	private function generatePasswd ($length) {
		$i = 0;
		$passwd = '';
		while ($i < $length) {
    			$passwd .= chr(rand(97, 122));
    			$i++;
		}
		$_SESSION['captcha'] = $passwd;
		return $passwd;
	}
	private function getFonts () {
		$font_path = dirname(__FILE__).'/fonts';
		$fonts = array();
		if ($handle = opendir($font_path)) {
		    while (false !== ($file = readdir($handle))) {
			if (substr(strtolower($file), -4, 4) == '.ttf') {
			    $fonts[] = $font_path . '/' . $file;
			}
		    }
		}
		return $fonts;
	}
	private function showImage ($passwd,$width,$height) {
		header ('Content-type: image/png');
		$im = @imagecreatetruecolor(120, 20) or die('Cannot Initialize new GD image stream');
		$text_color = imagecolorallocate($im, 233, 14, 91);
		imagestring($im, 1, 5, 5, $passwd, $text_color);
		imagepng($im);
		imagedestroy($im);
	}
}

?>

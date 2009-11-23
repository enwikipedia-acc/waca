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

// TODO: Add more fonts from http://openfontlibrary.org/

class captcha {
	public function generateId () {
		return $this->generatePasswd(10);
	}
	
	public function doCaptcha ($id) {
		$pwd = $this->generatePasswd(4);
		$this->removeExpiredData();
		$this->storeData($id,$pwd);
		$this->showImage($pwd,200,60);
	}
	
	private function storeData ($id,$code) {
		global $varfilepath;
		$expiry = time() + 3600; // expires in an hour
		file_put_contents($varfilepath.'captchas.txt',"$id $code $expiry\n",FILE_APPEND);
	}
	
	public function addFailedLogin () {
		global $varfilepath;
		$expiry = time() + 60*5; // expires in 5 minutes
		$ip = $_SERVER['REMOTE_ADDR'];
		file_put_contents($varfilepath.'failedlogins.txt',"$ip $expiry\n",FILE_APPEND);
	}
	
	public function clearFailedLogins () {
		global $varfilepath;
		$ip = $_SERVER['REMOTE_ADDR'];
		$text = explode("\n",file_get_contents($varfilepath.'failedlogins.txt'));
		$newtext = '';
		foreach ($text as $line) {
			if (preg_match('/^([0-9.]+) (\d+)$/',$line,$m)) {
				if ($m[1]!=$ip) {
					$newtext .= "$line\n";
				}
			}
		}
		file_put_contents($varfilepath.'failedlogins.txt',$newtext);
	}
	
	public function showCaptcha () {
		global $varfilepath;
		$this->removeExpiredData();
		$ip = $_SERVER['REMOTE_ADDR'];
		$text = explode("\n",file_get_contents($varfilepath.'failedlogins.txt'));
		foreach ($text as $line) {
			if (preg_match('/^([0-9.]+) (\d+)$/',$line,$m)) {
				if ($ip==$m[1]) {
					return true;
				}
			}
		}
		return false;
	}
	
	private function removeExpiredData () {
		global $varfilepath;
		$text = explode("\n",file_get_contents($varfilepath.'captchas.txt'));	
		$newtext = '';
		foreach ($text as $line) {
			if (preg_match('/(\d+)$/',$line,$m)) {
				if (time()<$m[1]) {
					$newtext .= "$line\n";
				}
			}
		}
		file_put_contents($varfilepath.'captchas.txt',$newtext);
		$text = explode("\n",file_get_contents($varfilepath.'failedlogins.txt'));
		$newtext = '';
		foreach ($text as $line) {
			if (preg_match('/(\d+)$/',$line,$m)) {
				if (time()<$m[1]) {
					$newtext .= "$line\n";
				}
			}
		}
		file_put_contents($varfilepath.'failedlogins.txt',$newtext);
	}
	
	public function verifyPasswd ($id,$passwd) {
		global $varfilepath;
		if (!preg_match('/^\w+$/i',$id)) {
			die('Invalid captcha id.');
		}
		$this->removeExpiredData();
		$text = explode("\n",file_get_contents($varfilepath.'captchas.txt'));
		foreach ($text as $line) {
			if (preg_match('/^(.+) (.+) (\d+)$/',$line,$m)) {
				if ($id==$m[1] and strtolower($passwd)==strtolower($m[2])) {
					$content = str_replace("$line\n",'',file_get_contents($varfilepath.'captchas.txt'));
					file_put_contents($varfilepath.'captchas.txt',$content);
					return true;
				}
			}
		}
		return false;
	}
	
	private function generatePasswd ($length) {
		$i = 0;
		$passwd = '';
		while ($i < $length) {
    			$passwd .= chr(rand(97, 122));
    			$i++;
		}
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
		$fonts = $this->getFonts();
		if (count($fonts) < 1) {
			die('No fonts loaded.');
		}

		header ('Content-type: image/png');
		$img = @imagecreatetruecolor($width,$height) or die('Cannot Initialize new GD image stream');
		// draw the backgroud
		$bg_colour = imagecolorallocate($img, rand(210,255), rand(210,255), rand(210,255));
		imagefilledrectangle($img,0,0,$width,$height,$bg_colour);
		
		$spacing = $width / (strlen($passwd)+2);
		$x = $spacing;
		// draw each character
		for ($i = 0; $i < strlen($passwd); $i++) {
		    $letter = $passwd[$i];
		    $size = rand($height/3, $height/2);
		    $rotation = rand(-30, 30);
		    $y = rand($height * .90, $height - $size - 4);
		    $font = $fonts[array_rand($fonts)];
		    $r = rand(100, 255); $g = rand(100, 255); $b = rand(100, 255);
		    $color = imagecolorallocate($img, $r, $g, $b);
		    $shadow = imagecolorallocate($img, $r/3, $g/3, $b/3);
		    imagettftext($img, $size, $rotation, $x, $y, $shadow, $font, $letter);
		    imagettftext($img, $size, $rotation, $x-1, $y-3, $color, $font, $letter);
		    $x += rand($spacing, $spacing * 1.5);  
		}

		imagepng($img);
		imagedestroy($img);
	}
}
?>
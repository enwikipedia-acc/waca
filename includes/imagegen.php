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
** SQL ( http://en.wikipedia.org/User:SQL )                 **
** Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
** FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
** Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
** Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
** Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
** OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
** Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
** Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**                                                           **
**************************************************************/

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

class imagegen {
	public function create($text) {
	
		// Font size of text.
		$font  = 2;
		
		// The size of the image.
		$width  = ImageFontWidth($font) * strlen($text) + 4;
		$height = ImageFontHeight($font) + 4;
		
		// Create a blank image with the above dimessions.
		$im = imagecreate ($width, $height);
		
		// Generate the colours to be used in the image.
		$background_color = imagecolorallocate ($im, 255, 255, 255);
		$text_color = imagecolorallocate ($im, 0, 0, 0);
		
		// Put it all together in the image.
		imagestring ($im, $font, 2, 2,  $text, $text_color);
		
		// Return the image.		
		return $im;
	}
}
?>

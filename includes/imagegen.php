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
	
		// Get the md5 hash of the provided text.
		// Md5 will always return the exact same value, for the exact same string.
		$id = md5($text);
		
		// Calculate the directory the image should be in, by taking the first letter of the hash
		$imageDir = './images/' . substr($id,0,1) . '/';
		
		// If the directory doesn't exist...
		if(! file_exists($imageDir) )
		{
			// ... make it!
			mkdir($imageDir);
		}
		
		// If there's already a file with tha name, why create it again?
		if( file_exists($imageDir.$id.'.png'))
		{
			// Return the id, that's enough to tell us where the image is stored.
			return $id;	
		}
		
		// Font size of text.
		$font  = 2;
		
		// The size of the image.
		$width  = ImageFontWidth($font) * strlen($text);
		$height = ImageFontHeight($font);
		
		// Create a blank image with the above dimessions.
		$im = imagecreate ($width, $height);
		
		// Generate the colours to be used in the image.
		// Uses the backgroud colour of the ACC as image background.
		$background_color = imagecolorallocate ($im, 248, 252, 255);
		$text_color = imagecolorallocate ($im, 0, 0, 0);
		
		// Put it all together in the image.
		imagestring ($im, $font, 0, 0,  $text, $text_color);
				
		// Writes the image to the system.
		imagepng($im, $imageDir . $id . '.png');
		
		// Return the id, so we can figure out where it's stored.
		return $id;
	}
}
?>

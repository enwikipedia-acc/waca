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

// accbot class
class accbotSend {
	public function send($message) {
		/*
		* Send to the IRC bot via UDP
		*/
		global $whichami, $ircBotUdpServer, $ircBotUdpPort;
		sleep(3);
		$message = html_entity_decode($message); // If a message going to the bot was for whatever reason sent through sanitze() earlier, reverse it. 
		$message = stripslashes($message);
		$fp = fsockopen("udp://" . $ircBotUdpServer, $ircBotUdpPort, $erno, $errstr, 30);
		if (!$fp) {
			echo "SOCKET ERROR: $errstr ($errno)<br />\n";
		}
		fwrite($fp, $this->formatForBot( chr(2)."[$whichami]".chr(2).": $message" ) );
		fclose($fp);
	}
	
	private function formatForBot( $data ) { 		
		global $ircBotCommunicationKey; 		
		$pData[0] = $this->encryptMessage( $data, $ircBotCommunicationKey ); 		
		$pData[1] = $data; 		
		$sData = serialize( $pData ); 		
		return $sData; 		
	} 	
	
	private function encryptMessage( $text, $key ) {
		$keylen = strlen($key);
		
		if( $keylen % 2 == 0 ) {
			$power = ord( $key[$keylen / 2] ) + $keylen;
		}
		else {
			$power = ord( $key[($keylen / 2) + 0.5] ) + $keylen;
		}
		
		$textlen = strlen( $text );
		while( $textlen < 64 ) {
			$text .= $text;
			$textlen = strlen( $text );
		}
		
		$newtext = null;
		for( $i = 0; $i < 64; $i++ ) {
			$pow = pow( ord( $text[$i] ), $power );
			$pow = str_replace( array( '+', '.', 'E' ), '', $pow );
			$toadd = dechex( substr($pow, -2) );
			while( strlen( $toadd ) < 2 ) {
				$toadd .= 'f';
			}
			if( strlen( $toadd ) > 2 ) $toadd = substr($toadd, -2);
			$newtext .= $toadd;
		}
		
		return $newtext;
	}
}

?>

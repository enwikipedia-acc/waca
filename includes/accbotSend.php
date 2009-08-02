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
**Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**                                                           **
**************************************************************/

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
		global $whichami;
		sleep(3);
		$fp = fsockopen("udp://91.198.174.211", 9001, $erno, $errstr, 30);
		if (!$fp) {
			echo "SOCKET ERROR: $errstr ($errno)<br />\n";
		}
		fwrite($fp, $this->formatForBot( chr(2)."[$whichami]".chr(2).": $message" ) );
		fclose($fp);
	}
	
	private function formatForBot( $data ) { 		
		global $ircBotCommunicationKey; 		
		$pData[0] = $ircBotCommunicationKey; 		
		$pData[1] = $data; 		
		$sData = serialize( $pData ); 		
		return $sData; 		
	}
}

?>

<?PHP
//die();
ini_set('display_errors',1);

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

	// Declares
	declare( ticks=1 );

	// Defines

	// Includes
	require 'config.inc.php';
	include 'devlist.php';

	global $ircBotNetworkHost, $ircBotNetworkPort, $ircBotChannel, $ircBotCommandTrigger, $ircBotNickname;

	// Variable declarations
	$pidnum = 0; // Integer
	$host = $ircBotNetworkHost;
	$port = $ircBotNetworkPort;
	$nick = $ircBotNickname;
	$ident = 'ACCBot';
	$chan = $ircBotChannel;
	$readbuffer = '';
	$realname = 'ACC Bot';
	$fp = null;
	$fpt = null;
        $sqlResource = null;


	// Signal handlers
	pcntl_signal( SIGHUP , 'SIGHUP'  );
	pcntl_signal( SIGTERM, 'SIGTERM' );
	pcntl_signal( SIGCHLD, 'SIGCHLD' );


	// Functions
	function sanitize( $data ) {
		$data = mysql_real_escape_string( $data );
		return htmlentities( $data, ENT_COMPAT, 'UTF-8' );
	}

	function SIGHUP() { /* Null signal handler */ }

	function SIGTERM() {
		global $fp, $fpt;

		fclose( $fp );
		fclose( $fpt );
		die( "Received SIGTERM.\n" );
	}

	function SIGCHLD() {
		echo 'In SIGCHLD ...' . "\n";
		while( pcntl_waitpid( 0, $status, WNOHANG ) > 0 ) {
			$status = pcntl_wexitstatus( $status );
		}
		echo 'Out SIGCHLD ...' . "\n";
	}

	function myq( $query ) {
		global $mysql, $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;

		if( !$sqlResource || !mysql_ping($sqlResource) ) {
			$sqlResource = mysql_connect( $toolserver_host, $toolserver_username, $toolserver_password, true );
			@mysql_select_db( $toolserver_database ) or print mysql_error();
		}

		return mysql_query( $query );
	}

	function irc( $data ) {
		global $fp;
		echo $data . "\n";
		fwrite( $fp, $data . "\r\n" );
	}

	function parseIrc( $line ) {
		global $commandTrigger;

		$return = array();

		$return['raw'] = $line;

		$explode1 = explode( ' ', $line, 4 );

		if( strlen( $line ) == 0 ) {
			$return['type'] = 'unknown';
			return;
		}

		if( strtolower( $explode1[0] ) == 'ping' ) {
			$return['type'] = 'ping';
			$return['payload'] = $explode1[1];
		} else {
			$return['type'] = 'unknown'; //Because other stuff is fun
		}

		return $return;
	}


	// Code entry point.

	if ( $_SERVER['REMOTE_ADDR'] != '' ) { 
		header( 'Location: http://localhost:8080/' );
		die(); 
	}

	$file = fopen("/tmp/ircbot.run", "w");
	fwrite($file, php_uname('n'));
	fclose($file);
	
	global $ircBotDaemonise;
	if($ircBotDaemonise)
	{
		$pidnum = pcntl_fork();
	 
		if( $pidnum == -1 ) {
			// Well, we can't daemonize for some reason.
			die( "Problem - Could not fork child!\n" );
		} else if( $pidnum ) {
			// We'll only be running a child process, so, this process need not continue.
			// echo "Detaching from terminal.\n";	
			exit();
		} else {
			// We're the child. Continuing on below as the child.
		}
	 
		if( posix_setsid() == -1 ) {
			// If we can't detach, we're not daemonized. No reason to go on.
			die( "Problem - I could not detach!\n" );
		}
	}
	
	set_time_limit( 0 );

	$fp = fsockopen( $host, $port, $errno, $errstr, 30 );
	if( !$fp ) {
		echo $errstr . ' (' . $errno . ")<br />\n";
	}

	global $ircBotNickServPassword;
	irc( 'PASS ' . $ircBotNickServPassword);
	irc( 'NICK ' . $nick );
	irc( 'USER ' . $ident . ' "' . $host . '" "localhost" :' . $realname );
	sleep( 5 );
	irc( 'JOIN ' . $chan );
	irc( 'JOIN #wikipedia-en-accounts-devs');

	// NOTIICATIONS START
	
	if( ( $udpReader = pcntl_fork() ) == 0 ) {
		$lastToolMsg = time();
		$lastToolMsgAlert = time();

		while( true ) {

			sleep(5);
			
			$rawdata = NULL;
			
			$sql = "SELECT notif_id, notif_text FROM acc_notif.notification WHERE notif_type = 1 ORDER BY notif_date ASC LIMIT 1;";
			$result = mysql_fetch_assoc(myq($sql));

//			print_r($result);

			if(isset($result))
			{

				$rawdata = $result["notif_text"];
//				var_dump($rawdata);

				myq("DELETE FROM acc_notif.notification WHERE notif_id = " . $result["notif_id"] . " LIMIT 1;");
			}
//			echo "after delete";
			if($rawdata == null)
			{
				
				continue;
			}
			
			irc( 'PRIVMSG ' . $chan . ' :' . str_replace( "\n", "\nPRIVMSG " . $chan . ' :', $rawdata ) );
			$lastToolMsg = time();
		}
		die();
	}
	// NOTIFICATIONS END
	
	// IRC START
	while( !feof( $fp ) ) {
		echo 'Begin parsing ...' . "\n";
	        $data = trim( fgets( $fp, 512 ) );

		echo 'Raw (' . strlen( $data ) . '): ' . $data . "\n";

		$parsed = parseIrc( $data );

		print_r( $parsed );

		if( ( isset( $parsed['type'] ) ) and ( $parsed['type'] == 'ping' ) ) {
	        	irc( 'PONG ' . $parsed['payload'] ); 
		}

		echo 'Done parsing ...' . "\n";
		
		//INSERT BY ROOT!!! --DaB.
		// Tweaked up to 2 --stw
		sleep(2);
	}
 	//IRC END
 	
	echo 'Ugh!' . "\n";

	// Ugh!  We most likely flooded off!

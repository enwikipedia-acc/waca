<?PHP

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
	$commandTrigger = $ircBotCommandTrigger;
	$fp = null;
	$fpt = null;
	$commands = array();
	$help = array();
	$users = array();
	$privgroups = array();
        $sqlResource = null;


	// Signal handlers
	pcntl_signal( SIGHUP , 'SIGHUP'  );
	pcntl_signal( SIGTERM, 'SIGTERM' );
	pcntl_signal( SIGCHLD, 'SIGCHLD' );

	// Help
	//       Command      , Parameters  , Description
	addHelp( 'help'       , ''          , 'Gives help on the available commands.'                               );
	addHelp( 'count'      , '<username>', 'Displays statistics for the targeted user.'                          );
	addHelp( 'status'     , ''          , 'Displays interface statistics, such as the number of open requests.' );
	addHelp( 'stats'      , '<username>', 'Gives a readout similar to the user list user information page.'     );
	addHelp( 'svninfo'    , ''          , 'Floods you with information about the SVN repository.'               );
	addHelp( 'sandinfo'   , ''          , 'Floods you with information about the SVN repository sandbox.'       );
	addHelp( 'rewrite-pull' , ''		, 'Allows developers to sync the rewrite sandbox with the master branch.');
	addHelp( 'rewrite-svnup' , ''		, 'Alias of rewrite-pull'												);
	addHelp( 'sand-pull'  , ''			, 'Alias of sand-svnup'													);
	addHelp( 'sand-svnup' , ''          , 'Allows developers to sync the sandbox with the SVN repository.'      );
	addHelp( 'restart'    , ''          , 'Causes the bot to do an immediate graceful reinitialization.'        );
	
	// Commands
	//          Command      	, Function            	, Fork?
	addCommand( 'help'       	, 'commandHelp'       	, true  );
	addCommand( 'count'      	, 'commandCount'      	, false );
	addCommand( 'status'     	, 'commandStatus'     	, false );
	addCommand( 'stats'      	, 'commandStats'      	, false );
	addCommand( 'svninfo'    	, 'commandSvnInfo'    	, true  );
	addCommand( 'sandinfo'   	, 'commandSandInfo'   	, true  );
	addCommand( 'rewrite-svnup'	, 'commandRewriteSvnUp'	, true  );
	addCommand( 'rewrite-pull'	, 'commandRewriteSvnUp'	, true  );
	addCommand( 'sand-pull'		, 'commandSandSvnUp'	, true  );
	addCommand( 'sand-svnup' 	, 'commandSandSvnUp'  	, true  );
	addCommand( 'restart'    	, 'commandRestart'    	, false );

	// Users
	// Nick!User@Host mask                 			=> Group
	$users = array(
		'*!*@cobi.cluenet.org'						=> 'developer',
		'*!*@Cobi.cluenet.org'						=> 'developer',
		'*!*@2002:1828:88fb:0:aede:48ff:febe:ef03'	=> 'developer', //Cobi.
		'*!*@wikipedia/SQL'							=> 'developer', 
		'*!*@mediawiki/OverlordQ'	  				=> 'developer',
		'*!*@pdpc/supporter/student/stwalkerster'	=> 'developer',
		'*!*@*Alexfusco5'			   				=> 'developer',
		'*!*@wikipedia/Soxred93'					=> 'developer',
		'*!*@yourwiki/staff/charlie'				=> 'developer',
		'*!*@wikipedia/pdpc.active.FastLizard4'		=> 'developer',
		'*!*@wikipedia/Prodego'						=> 'developer',
		'*!*@wikipedia/FunPika'						=> 'developer',
		'*!*@wikimedia/Promethean'					=> 'developer',
		'*!*@wikipedia/Chris-G'						=> 'developer',
		'*!*@unaffiliated/louriepieterse'			=> 'developer',
		'*!*@wikipedia/EdoDodo'						=> 'developer',
        '*!*@wikipedia/Mr-R00t'    					=> 'developer',
		'*!*@wikipedia/DeltaQuad' 					=> 'developer',
		'*!*@wikipedia/Manishearth' 				=> 'developer',
		'*!*@Wikipedia/MacMed'						=> 'developer',
		
		'Helpmebot!~hmb6@wikimedia/bot/helpmebot'	=> 'bot',
                'Helpmebot!helpmebot@wikimedia/bot/helpmebot'       => 'bot',
		'*!*@*'										=> '*'
		);

	// Groups
	//         [ Group       ][ Privilege     ] = 1;
	$privgroups[ '*'         ][ 'help'        ] = 1;
	$privgroups[ '*'         ][ 'count'       ] = 1;
	$privgroups[ '*'         ][ 'status'      ] = 1;
	$privgroups[ '*'         ][ 'stats'       ] = 1;
	$privgroups[ '*'         ][ 'svninfo'     ] = 1; //Do not change this, per consensus in the IRC channel.
	$privgroups[ '*'         ][ 'sandinfo'    ] = 1;

	$privgroups[ 'developer' ] = $privgroups['*']; // Developer group inherits '*'.
	$privgroups[ 'developer' ][ 'sand-svnup'  	] = 1;
	$privgroups[ 'developer' ][ 'rewrite-svnup' ] = 1;
	$privgroups[ 'developer' ][ 'sand-pull' 	] = 1;
	$privgroups[ 'developer' ][ 'rewrite-pull' 	] = 1;
	$privgroups[ 'developer' ][ 'restart'		] = 1;
	
	$privgroups[ 'bot'		 ][ 'sand-svnup'  	] = 1;
	$privgroups[ 'bot'		 ][ 'rewrite-pull' 	] = 1;

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

	function addCommand( $command, $callback, $forked = false ) {
		global $commands;

		$commands[ strtolower( $command ) ] = array( $callback, $forked );
	}

	function doCommand( $command, $parsed ) {
		global $commands;

		if( isset( $commands[ strtolower( $command ) ] ) ) {
			$info = $commands[ strtolower( $command ) ];
			if( hasPriv( strtolower( $command ), $parsed ) ) {
				if( $info[1] == true ) {
					if( pcntl_fork() == 0 ) {
						if( function_exists( $info[0] ) ) call_user_func( $info[0], $parsed );
						die();
					}
				} else {
					if( function_exists( $info[0] ) ) call_user_func( $info[0], $parsed );
				}
			} else {
				irc( 'NOTICE ' . $parsed['nick'] . ' :Insufficient access.' );
			}
		}
	}

	function addHelp( $command, $parameters, $description ) {
		global $help;

		$help[ strtolower( $command ) ] = array( $parameters, $description );
	}

	function getHelp( $parsed ) {
		global $help;

		$return = array();

		foreach( $help as $command => $info ) {
			if( hasPriv( $command, $parsed ) ) {
				$return[] = array(
					'command' => $command,
					'params'  => $info[0],
					'desc'    => $info[1]
					);
			}
		}

		return $return;
	}

	function hasPriv( $priv, $parsed ) {
		global $privgroups, $users;

		foreach( $users as $user => $group ) {
			if( fnmatch( $user, $parsed['n!u@h'] ) ) {
				if( isset( $privgroups[$group][$priv] ) ) {
					return $privgroups[$group][$priv];
				} else {
					return 0;
				}
			}
		}
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
		} else if( strtolower( $explode1[1] ) == 'privmsg' ) {
			$return['type'] = 'privmsg';
			$return['n!u@h'] = ( ( $explode1[0]{0} == ':' ) ? substr( $explode1[0], 1 ) : $explode1[0] );
			$return['nick'] = explode( '!', $return['n!u@h'] );
			$return['user'] = explode( '@', $return['nick'][1] );
			$return['host'] = $return['user'][1];
			$return['user'] = $return['user'][0];
			$return['nick'] = $return['nick'][0];
			$return['realto'] = $explode1[2];
			$return['to'] = strtolower( $return['realto'] );
			$return['message'] = ( ( $explode1[3]{0} == ':' ) ? substr( $explode1[3], 1 ) : $explode1[3] );
			$return['words'] = explode( ' ', $return['message'] );
			if( $return['message']{0} == $commandTrigger ) {
				$return['trigger'] = $commandTrigger;
				$return['command'] = explode( ' ', substr( $return['message'], 1 ), 2 );
				if( isset( $return['command'][1] ) ) $return['parameter'] = $return['command'][1];
				if( isset( $return['parameter'] ) ) $return['parameters'] = explode( ' ', $return['parameter'] );
				$return['command'] = $return['command'][0];
			}
			
			
			global $ircBotNickname;
			if($return['to'] == $ircBotNickname)
			{
				$return['to'] = $return['nick'];
			}
			
		} else {
			$return['type'] = 'unknown'; //Because other stuff is fun
		}

		return $return;
	}

	// Command functions
	function commandHelp( $parsed ) {
		global $chan;
		irc( 'NOTICE ' . $parsed['nick'] . ' :Available commands (all should be run in ' . $chan . '):' );
		sleep( 1 );
		foreach( getHelp( $parsed ) as $info ) {
			irc( 'NOTICE ' . $parsed['nick'] . ' :' . $parsed['trigger'] . $info['command'] . ' ' . $info['params'] . ' - ' . $info['desc'] );
			sleep( 3 );
		}
	}

	function commandCount( $parsed ) {
		$username = isset( $parsed['parameter'] ) ? $parsed['parameter'] : '';
		if( $username == '' ) {
			//irc( 'NOTICE ' . $parsed['nick'] . ' :Invalid syntax.  This command requires a username as a parameter.' );
			//return;
			
			//make the bot use the caller's nick if no username specified.
			if (preg_match('/^wiki[mp]edia\/(.+)$/',$parsed['host'],$m)) { /* Try to parse the name from a cloak */
				$username = $m[1];
			} else { 
				$username = $parsed['nick'];
			}
			// note: this is a bit of a test, and hopefully won't have too much of an impact on this function. I hope I've got this right. 
			// old code is the 2 lines above (commented out). Regards, Stwalkerster.
		}
		$username = trim($username); //Strip any whitespace from the username.  

		$isUser = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_user` WHERE `user_name` = \'' . mysql_real_escape_string( $username ) . '\'' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );

		$isUser = ( ( $isUser['count'] == 0 ) ? false : true );

		if( $isUser ) {
			$count = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE (`log_action` = \'Closed 1\' OR `log_action` = \'Closed custom-y\') AND `log_user` = \''
				. mysql_real_escape_string( $username ) . '\'' ) ) or die( 'MySQL Error: ' . mysql_error() . "\n" );

			$count = $count['count'];

			$user = mysql_fetch_assoc( myq( 'SELECT * FROM `acc_user` WHERE `user_name` = \'' . mysql_real_escape_string( $username ) . '\'' ) )
				or die( 'MySQL Error: ' . mysql_error() . "\n" );

			$adminInfo = '';
			if( $user['user_level'] == 'Admin' ) {
				$sus = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_user` = \''
					. mysql_real_escape_string( $username ) . '\' AND `log_action` = \'Suspended\'' ) )
					or die( 'MySQL Error: ' . mysql_error() . "\n" );
				$sus = $sus['count'];
 
				$pro = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_user` = \''
					. mysql_real_escape_string( $username ) . '\' AND `log_action` = \'Promoted\'' ) )
					or die( 'MySQL Error: ' . mysql_error() . "\n" );
				$pro = $pro['count'];

				$app = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_user` = \''
					. mysql_real_escape_string( $username ) . '\' AND `log_action` = \'Approved\'' ) )
					or die( 'MySQL Error: ' . mysql_error() . "\n" );
				$app = $app['count'];

				$dem = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_user` = \''
					. mysql_real_escape_string( $username ) . '\' AND `log_action` = \'Demoted\'' ) )
					or die( 'MySQL Error: ' . mysql_error() . "\n" );
				$dem = $dem['count'];

				$dec = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_user` = \''
					. mysql_real_escape_string( $username ) . '\' AND `log_action` = \'Declined\'' ) )
					or die( 'MySQL Error: ' . mysql_error() . "\n" );
				$dec = $dec['count'];
                                
                                $rnc = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_user` = \''
					. mysql_real_escape_string( $username ) . '\' AND `log_action` = \'Renamed\'' ) )
					or die( 'MySQL Error: ' . mysql_error() . "\n" );
				$rnc = $rnc['count'];

                                $mec = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_user` = \''
					. mysql_real_escape_string( $username ) . '\' AND `log_action` = \'Edited\'' ) )
					or die( 'MySQL Error: ' . mysql_error() . "\n" );
				$mec = $mec['count'];

                                $pcc = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_user` = \''
					. mysql_real_escape_string( $username ) . '\' AND `log_action` = \'Prefchange\'' ) )
					or die( 'MySQL Error: ' . mysql_error() . "\n" );
				$pcc = $pcc['count'];                                     

				$adminInfo = 'Suspended: ' . $sus . ', Promoted: ' . $pro . ', Approved: ' . $app . ', Demoted: ' . $dem . ', Declined: ' . $dec . ', Renamed: ' . $rnc . ', Messages Edited: ' . $mec . ', Preferences Edited: ' . $pcc;
			}

			$today = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_log` WHERE `log_time` LIKE \'' . sanitize( date( 'Y-m-d' ) )
				. '%\' AND (`log_action` = \'Closed 1\' OR `log_action` = \'Closed custom-y\') AND `log_user` = \'' . mysql_real_escape_string( $username ) . '\'' ) )
				or die( 'MySQL Error: ' . mysql_error() . "\n" );
			$today = $today['count'];

			irc( 'PRIVMSG ' . $parsed['to'] . ' :' . html_entity_decode($username) . ' (' . $user['user_level'] . ') has closed ' . $count
				. ' requests as \'Created\', ' . ( ( $today == 0 ) ? 'none' : $today ) . ' of them today. ' . $adminInfo );
		} else {
			irc( 'PRIVMSG ' . $parsed['to'] . ' :' . html_entity_decode($username) . ' is not a valid username.' );
		}
	}

	function commandStatus( $parsed ) {
		$open = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_status` = \'Open\' AND `pend_mailconfirm` = \'Confirmed\'' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );
		$open = $open['count'];

		$adminRequests = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_status` = \'Admin\' AND `pend_mailconfirm` = \'Confirmed\'' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );
		$adminRequests = $adminRequests['count'];
		
		$cuRequests = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_status` = \'Checkuser\' AND `pend_mailconfirm` = \'Confirmed\'' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );
		$cuRequests = $cuRequests['count'];

		$bans = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_ban`' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );
		$bans = $bans['count'];

		$admins = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_user` WHERE `user_level` = \'Admin\'' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );
		$admins = $admins['count'];

		$users = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_user` WHERE `user_level` = \'User\'' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );
		$users = $users['count'];

		$new = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_user` WHERE `user_level` = \'New\'' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );
		$new = $new['count'];

		irc( 'PRIVMSG ' . $parsed['to'] . ' :'
			. 'Open requests: ' . $open
			. ', Flagged user needed requests: ' . $adminRequests
			. ', Checkuser requests: ' . $cuRequests
			. ', Banned: ' . $bans
			. ', Site users: ' . $users
			. ', Site admins: ' . $admins
			. ', Awaiting approval: ' . $new );
	}

	function commandStats( $parsed ) {
		$username = $parsed['parameter'];
		if( !isset( $username ) or ( $username == '' ) ) {
			$username = $parsed['nick'];
		}
		$username = trim($username); //Strip any whitespace from the username.

		$isUser = mysql_fetch_assoc( myq( 'SELECT COUNT(*) AS `count` FROM `acc_user` WHERE `user_name` = \'' . sanitize( $username ) . '\'' ) )
			or die( 'MySQL Error: ' . mysql_error() . "\n" );

		$isUser = ( ( $isUser['count'] == 0 ) ? false : true );

		if( $isUser ) {
			$user = mysql_fetch_assoc( myq( 'SELECT * FROM `acc_user` WHERE `user_name` = \'' . sanitize( $username ) . '\'' ) )
				or die( 'MySQL Error: ' . mysql_error() . "\n" );

			irc( 'PRIVMSG ' . $parsed['to'] . ' :' . html_entity_decode($username) . ' (' . $user['user_level'] . ') was last active '
				. ( ( $user['user_lastactive'] == '0000-00-00 00:00:00' ) ? 'unknown' : $user['user_lastactive'] )
				. '. He/she currently has automatic welcoming of users ' . ( ( $user['user_welcome_templateid'] == 0 ) ? 'disabled' : 'enabled' )
				. '. His/her onwiki username is [[User:' . $user['user_onwikiname'] . ']].' );

		} else {
			irc( 'PRIVMSG ' . $parsed['to'] . ' :' . html_entity_decode($username) . ' is not a valid username.' );
		}
	}

	function commandSandInfo( $parsed ) {
		$svn = popen( 'cd sand; svn info 2>&1', 'r' );
		while( !feof( $svn ) ) {
			$svnin = trim( fgets( $svn, 512 ) );
			if( $svnin != '' ) {
				irc( 'NOTICE ' . $parsed['nick'] . ' :' . str_replace( array( "\n", "\r" ), '', $svnin ) );
			}
			sleep( 3 );
		}
		pclose( $svn );
	}

	function commandSvnInfo( $parsed ) {
		$svn = popen( 'svn info 2>&1', 'r' );
		while( !feof( $svn ) ) {
			$svnin = trim( fgets( $svn, 512 ) );
			if( $svnin != '' ) {
				irc( 'NOTICE ' . $parsed['nick'] . ' :' . str_replace( array( "\n", "\r" ), '', $svnin ) );
			}
			sleep( 3 );
		}
		pclose( $svn );
	}

	function commandSandSvnUp( $parsed ) {
		$svn = popen( 'sh svn-sand.sh 2>&1', 'r' );
		while( !feof( $svn ) ) {
			$svnin = trim( fgets( $svn, 512 ) );
			if( $svnin != '' ) {
				irc( 'PRIVMSG ' . $parsed['to'] . ' :' . $parsed['nick'] . ': ' . str_replace( array( "\n", "\r" ), '', $svnin ) );
			}
			sleep( 1 );
		}
		pclose( $svn );
		irc( 'PRIVMSG ' . $parsed['to'] . ' :' . $parsed['nick'] . ': Please see the sandbox at http://toolserver.org/~acc/sand/acc.php' );
	}

	function commandRewriteSvnUp( $parsed ) {
        $svn = popen( 'sh svn-rewrite.sh 2>&1', 'r' );
        while( !feof( $svn ) ) {
            $svnin = trim( fgets( $svn, 512 ) );
        	if( $svnin != '' ) {
            	irc( 'PRIVMSG ' . $parsed['to'] . ' :' . $parsed['nick'] . ': ' . str_replace( array( "\n", "\r" ), '', $svnin ) );
            }
        	sleep( 1 );
        }
        pclose( $svn );
    	irc( 'PRIVMSG ' . $parsed['to'] . ' :' . $parsed['nick'] . ': Please see the acc.php rewrite sandbox at http://toolserver.org/~acc/rewrite/acc.php' );
	}             

	
	function commandRestart( $parsed ) {
		global $udpReader, $fp;

		fclose( $fp );

		posix_kill( $udpReader, SIGTERM );
		sleep( 2 );
		posix_kill( $udpReader, SIGKILL );
		sleep( 5 );
		
		exit(0); // we let the jobserver reboot the bot.
	}

	function validateData( $sdata ) {
		global $ircBotCommunicationKey;
		echo "Data Recieved! $sdata\n";
		$data = unserialize( ltrim(rtrim( $sdata ) ) );
		if( $data === false ) {
			echo "WARNING: Invalid data - could not unserialize.\n";
			return false;
		}
		if( $data[0] != encryptMessage( $data[1], $ircBotCommunicationKey ) ) {
			echo "WARNING: INVALID DATA!\n";
			echo "$sdata\n";
			return false;
		} else { 
			echo "Valid UDP packet received\n";
			return true;
		}
	}	

	// Code entry point.

	if ( $_SERVER['REMOTE_ADDR'] != '' ) { 
		header( 'Location: http://toolserver.org/~acc/' );
		die(); 
	}

	$file = fopen("/home/project/a/c/c/acc/ircbot.run", "w");
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

	myq('SELECT 1');
	
	// NOTIICATIONS START
	
	if( ( $udpReader = pcntl_fork() ) == 0 ) {
		$lastToolMsg = time();
		$lastToolMsgAlert = time();
		global $ircBotSnsArn;

		while( true ) {
			
			$rawdata = getNotificationFromSQS();
			if($rawdata == null)
			{
				sleep(5); // give AWS a chance! :P
				continue;
			}
			$data = $rawdata["Message"];
			
			if( $data != '' ) {
				if( validateData( $data ) ) {
					$uData = unserialize( $data );
					irc( 'PRIVMSG ' . $chan . ' :' . str_replace( "\n", "\nPRIVMSG " . $chan . ' :', $uData[1] ) );
					$lastToolMsg = time();
				}
			}
			if ((time() - $lastToolMsg) > 3600*6) {
				// only send alerts every fifteen minutes so we don't piss people off too much
				if ((time() - $lastToolMsgAlert) > 60*15) {
					$lastToolMsgAlert = time();
					irc('PRIVMSG '.$chan.' :Alert, I haven\'t received any data from the acc tool in over six hours, please check that everything is ok and nothing is broken.');
				}
			}
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

		if( ( isset( $parsed['type'] ) ) and ( $parsed['type'] == 'privmsg' ) ) {
			if( $parsed['to'] == strtolower( $chan ) || $parsed['to'] == strtolower( "#wikipedia-en-accounts-devs" ) ) {
				if( isset( $parsed['command'] ) ) {
					doCommand( $parsed['command'], $parsed );
				}
			}
		}
		echo 'Done parsing ...' . "\n";
	}
 	//IRC END
 	
	echo 'Ugh!' . "\n";

	// Ugh!  We most likely flooded off!

	commandRestart( null );
	
function encryptMessage( $text, $key ) {
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

function getNotificationFromSQS()
{
	$sqs = new AmazonSQS();
	$response = $sqs->get_queue_list("/accbot-alert-queue/");
	$getresponse = $sqs->receive_message($response[0], array("MaxNumberOfMessages" => 1));
	if(!$getresponse->isOk())
	{
		var_dump($getresponse);
	    return NULL;
	}
	
	$jsonmessage=($getresponse->body->Body(0));
	$receipthandle = $getresponse->body->ReceiptHandle(0);
	$sqs->delete_message($response[0], $receipthandle);
	
	return json_decode($jsonmessage,true);
}
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
	**Prodego ( http://en.wikipedia.org/wiki/User:Prodego )      **
	**FunPika ( http://en.wikipedia.org/wiki/User:FunPika )      **
	**                                                           **
	**************************************************************/

/*****************************************************************
 *Configuration file for ACCBot.
 *Contains access lists, access rights, help commands.  Only the
 *!rehash command or a SIGHUP to the bot is all that is required
 *to modify these elements, a !restart is no longer needed
*****************************************************************/
	// Help
	//       Command      , Parameters  , Description
	addHelp( 'help'       , ''          , 'Gives help on the available commands.'                               );
	addHelp( 'count'      , '<username>', 'Displays statistics for the targeted user.'                          );
	addHelp( 'status'     , ''          , 'Displays interface statistics, such as the number of open requests.' );
	addHelp( 'stats'      , '<username>', 'Gives a readout similar to the user list user information page.'     );
	addHelp( 'svninfo'    , ''          , 'Floods you with information about the SVN repository.'               );
	addHelp( 'sandinfo'   , ''          , 'Floods you with information about the SVN repository sandbox.'       );
	addHelp( 'sand-svnup' , ''          , 'Allows developers to sync the sandbox with the SVN repository.'      );
	addHelp( 'php'        , '<file>'    , 'Allows developers to check for errors in PHP files.'                 );
	addHelp( 'svnup'      , ''          , 'Allows you to sync the live server with the SVN repository.'         );
	addHelp( 'restart'    , ''          , 'Causes the bot to do an immediate graceful reinitialization.'        );
	addHelp( 'recreatesvn', ''          , 'Attempts to fix the live copy of the site.'                          );
	addHelp( 'rehash'     , ''          , 'Rehashes the bot\'s access list.'                                    );

	// Users
	//	Nick!User@Host mask						=> group
	$users = array(
		'Cobi!*cobi*@cobi.cluenet.org'					=> 'root',
		'Cobi!*cobi*@Cobi.cluenet.org'					=> 'root',
		'*!*@2002:1828:8399:4000:21f:3bff:fe10:4ae3'			=> 'root',
		'*!*@wikipedia/SQL'						=> 'root',
		'OverlordQ!*@wikipedia/OverlordQ'				=> 'root',
		'Stwalkerster*!*@wikipedia/Stwalkerster'			=> 'root',
		'*!*@wikipedia/Alexfusco5'					=> 'developer',
		'[X]*!*@wikipedia/Soxred93'				=> 'developer',
		'*!*@tangocms/developer/chuck'					=> 'developer',
		'*!*@wikipedia/FastLizard4'					=> 'developer',
		'*!*@wikipedia/Prodego'					        => 'developer',
		'*!*@wikipedia/FunPika'					        => 'developer',
		'*!*@*'								=> '*'
		);

	// Groups
	//         [ Group       ][ Privilege     ] = 1;
	$privgroups[ '*'         ][ 'help'        ] = 1;
	$privgroups[ '*'         ][ 'count'       ] = 1;
	$privgroups[ '*'         ][ 'status'      ] = 1;
	$privgroups[ '*'         ][ 'stats'       ] = 1;
	$privgroups[ '*'         ][ 'svninfo'     ] = 1; //Do not change this, per consensus in the IRC channel
	$privgroups[ '*'         ][ 'sandinfo'    ] = 1;

	$privgroups[ 'developer' ]                  = $privgroups['*']; // 'developer' inherits '*'.
	$privgroups[ 'developer' ][ 'sand-svnup'  ] = 1;
	$privgroups[ 'developer' ][ 'php'  ]        = 1;

	$privgroups[ 'root'      ]                  = $privgroups['developer']; // 'root' inherits 'developer'.
	$privgroups[ 'root'      ][ 'svnup'       ] = 1;
	$privgroups[ 'root'      ][ 'recreatesvn' ] = 1;
	$privgroups[ 'root'      ][ 'restart'     ] = 1;
	$privgroups[ 'root'      ][ 'rehash'      ] = 1;

?>

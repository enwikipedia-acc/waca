<?
if (isset($_SERVER['REQUEST_METHOD'])) {
	die();
} // Web clients die.
ini_set('display_errors', 1);
require_once 'config.inc.php';
require_once 'includes/PdoDatabase.php';

function entryFromString($line) {
	// Function adapted from MediaWiki's source code. Original function can be found at:
	// http://svn.wikimedia.org/svnroot/mediawiki/trunk/extensions/TitleBlacklist/TitleBlacklist.list.php
	$options = array();
	$line = preg_replace ("/^\\s*([^#]*)\\s*((.*)?)$/", "\\1", $line);
	$line = trim ($line);
	preg_match('/^(.*?)(\s*<([^<>]*)>)?$/', $line, $pockets);
	@list($full, $regex, $null, $opts_str) = $pockets;
	$regex = trim($regex);
	$regex = str_replace('_', ' ', $regex);
	$opts_str = trim($opts_str);
	$opts = preg_split('/\s*\|\s*/', $opts_str);
	$casesensitive = false;
	foreach ($opts as $opt) {
		$opt2 = strtolower($opt);
		if ($opt2 == 'moveonly') {
			return null;
		}
		if ($opt2 == 'casesensitive') {
			$casesensitive = true;
		}
	}
	if ($regex) {
		return array($regex, $casesensitive);
	} else {
		return null;
	}
}

$queryresult = unserialize(file_get_contents("http://en.wikipedia.org/w/api.php?action=query&format=php&prop=revisions&titles=MediaWiki:Titleblacklist&rvprop=content"));
$queryresult = current($queryresult['query']['pages']);

$text = $queryresult['revisions'][0]['*'];
$lines = preg_split("/\r?\n/", $text);
$result = array();
foreach ($lines as $line) {
	$line = entryFromString($line);
	if ($line) {
		$entries[] = $line;
	}
}

$db = gGetDb( );

$sanitycheck=array();

if( $db->beginTransaction() )
{
	$deleteQuery = $db->exec("DELETE FROM `acc_titleblacklist`;");
	if( $deleteQuery === false )
	{
		$db->rollback();
		print_r( $db->errorInfo() );
		echo "Error in transaction.\n";
		exit( 1 );
	}	
	
	$insertQuery = $db->prepare( "INSERT INTO acc_titleblacklist (titleblacklist_regex, titleblacklist_casesensitive) VALUES (:regex, :case);" );
	foreach ($entries as $entry) {
		list($regex, $casesensitive) = $entry;
		
		if(array_key_exists($regex, $sanitycheck))
			continue;
			
		$sanitycheck[$regex]=1;
		
		$params = array(
			":regex" => $regex,
			":case" => $casesensitive
		);
		
		$success2 = $insertQuery->execute( $params );
		
		if(!$success2)
		{
			$db->rollback();
			print_r( $db->errorInfo() );
			echo "Error in transaction.\n";
			exit( 1 );
		}
	}
	
	$db->commit();
	echo "The title blacklist table has been recreated.\n";
}
else
	echo "Error starting transaction.\n";
?>

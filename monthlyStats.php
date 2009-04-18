<?php
require_once('config.inc.php');
require_once('functions.php');
require_once('libchart/classes/libchart.php');

readOnlyMessage();

session_start();

// retrieve database connections
global $tsSQLlink, $asSQLlink, $toolserver_database;
list($tsSQLlink, $asSQLlink) = getDBconnections();
@ mysql_select_db($toolserver_database, $tsSQLlink);

if( isset( $_SESSION['user'] ) ) {
	$sessionuser = $_SESSION['user'];
} else {
	$sessionuser = "";
}

if( !(hasright($sessionuser, "Admin") || hasright($sessionuser, "User")))
{
	die("You are not authorized to use this feature. Only logged in users may use this statistics page.");
}

echo makehead( $sessionuser ) . '<div id="content"><h2>Requests Closed per Month</h2>';

$qb = new QueryBrowser();

$query = "SELECT COUNT(DISTINCT log_id) AS 'Requests Closed', YEAR(log_time) AS 'Year', MONTHNAME(log_time) AS 'Month' FROM acc_log WHERE log_action LIKE 'Closed%' GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;";

echo $qb->executeQueryToTable($query);

global $showGraphs;
if($showGraphs == 1)
{
	$gquery = "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), ' ' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed%' GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;";

	// draw the graph
	$chart = new LineChart(500, 250);
	$series = new XYDataSet();
	foreach($qb->executeQueryToArray($gquery) as $row)
	{
		$series->addPoint(new Point($row['x'], $row['y']));
	}

	$chart->setDataSet($series);

	$chart->setTitle("Monthly Closed Requests");
	$chart->render('render/'.$_SERVER['REQUEST_TIME']);

	echo '<img src="render/'.$_SERVER['REQUEST_TIME'].'" />';
}
else
{
	echo "Graph drawing is currently disabled.";
}
echo showfooter();
?>
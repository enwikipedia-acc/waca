<?php
require_once('config.inc.php');
require_once('functions.php');
//require_once('libchart/classes/libchart.php');
require_once('graph/pChart/pChart.class');
require_once('graph/pChart/pData.class');


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

//	// draw the graph

	$DataSet = new pData;
//	$chart = new LineChart(500, 250);
//	$series = new XYDataSet();
	foreach($qb->executeQueryToArray($gquery) as $row)
	{
//		$series->addPoint(new Point($row['x'], $row['y']));
		$DataSet->AddPoint($row['y'], "Serie1");
	}
	$DataSet->AddAllSeries();
	$DataSet->SetAbsciseLabelSerie();
	$DataSet->SetSerieName("Closed requests","Serie1");
//
//	$chart->setDataSet($series);
//
//	$chart->setTitle("Monthly Closed Requests");
//	$chart->render('render/'.$_SERVER['REQUEST_TIME']);

	$Test = new pChart(700,230);
$Test->setFontProperties("graph/Fonts/tahoma.ttf",8);
$Test->setGraphArea(50,30,585,200);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the 0 line
$Test->setFontProperties("graph/Fonts/tahoma.ttf",6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Draw the cubic curve graph
$Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,50);

// Finish the graph
$Test->setFontProperties("graph/Fonts/tahoma.ttf",8);
$Test->drawLegend(600,30,$DataSet->GetDataDescription(),255,255,255);
$Test->setFontProperties("graph/Fonts/tahoma.ttf",10);
$Test->drawTitle(50,22,"Monthly Statistics",50,50,50,585);
$Test->Render("render/".$_SERVER['REQUEST_TIME']);   
	

	echo '<img src="render/'.$_SERVER['REQUEST_TIME'].'" />';
}
else
{
	echo "Graph drawing is currently disabled.";
}
echo showfooter();
?>
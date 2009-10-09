<?php
class StatsMonthlyStats extends StatisticsPage
{
	function execute()
	{
		$qb = new QueryBrowser();
	
		$query = "SELECT COUNT(DISTINCT log_id) AS 'Requests Closed', YEAR(log_time) AS 'Year', MONTHNAME(log_time) AS 'Month' FROM acc_log WHERE log_action LIKE 'Closed%' GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;";
		
		$out = $qb->executeQueryToTable($query);
		
		global $showGraphs;
		if($showGraphs == 1)
		{
			global $filepath;
			require_once($filepath . 'graph/pChart/pChart.class');
			require_once($filepath . 'graph/pChart/pData.class');
			
			createGraph();	
			$out.= '<img src="render/'.$_SERVER['REQUEST_TIME'].'" />';
		}
		else
		{
			$out.= "<br />Graph drawing is currently disabled.";
		}
		
		return $out;
	}
	function getPageName()
	{
		return "MonthlyStats";
	}
	function getPageTitle()
	{
		return "Monthly Statistics";
	}
	function isProtected()
	{
		return true;
	}
	
	function createGraph()
	{
		$gquery = "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), ' ' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed%' GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;";
	
		$DataSet = new pData;
		foreach($qb->executeQueryToArray($gquery) as $row)
		{
			$DataSet->AddPoint($row['y'], "Serie1");
		}
		$DataSet->AddAllSeries();
		$DataSet->SetAbsciseLabelSerie();
		$DataSet->SetSerieName("Closed requests","Serie1");
	
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
	}
	
	function requiresWikiDatabase()
	{
		return false;	
	}
	
}

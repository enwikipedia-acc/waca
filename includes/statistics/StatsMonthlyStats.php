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
			
			$queries = array(
				array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed%' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "All closed requests by month"
				),
				array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed 0' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "Dropped requests by month"
				),
				/*array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed 1' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "Created"
				),*/
				array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed 2' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "Similar requests by month"
				),
				array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed 3' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "Taken requests by month"
				),
				array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed 4' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "UVio requests by month"
				),
				array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed 5' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "Technical requests by month"
				),
				array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed 26' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "SUL Taken requests by month"
				),
				array(
					'query' => "SELECT COUNT(DISTINCT log_id) AS 'y', CONCAT( YEAR(log_time), '/' , MONTHNAME(log_time)) AS 'x' FROM acc_log WHERE log_action LIKE 'Closed custom' AND YEAR(log_time) != 0 GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;",
					'series' => "Custom requests by month"
				)
			);

			foreach ($this->createClosuresGraph($queries) as $i) {
				$out.= '<img src="render/' . $i . '" />';
			}

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
	function requiresWikiDatabase()
	{
		return false;	
	}

	function createClosuresGraph($queries)
	{
		$qb = new QueryBrowser();
		
		$imagehashes = array();		
		
		foreach ($queries as $q) {
			$DataSet = new pData();
			$qResult = $qb->executeQueryToArray($q['query']);
			
			if(sizeof($qResult) > 0)
			{
			
				foreach($qResult as $row)
				{
					$DataSet->AddPoint($row['y'], $q['series'], $row['x']);
				}
	
				
				
				$DataSet->AddAllSeries();
				$DataSet->SetAbsciseLabelSerie();
				
				$chartname = md5(serialize($DataSet));
				$imagehashes[] = $chartname;
				
				if(!file_exists($chartname))
				{
					$Test = new pChart(700,280);
					$Test->setFontProperties("graph/Fonts/tahoma.ttf",8);
					$Test->setGraphArea(50,30,680,200);
					$Test->drawFilledRoundedRectangle(7,7,693,273,5,240,240,240);
					$Test->drawRoundedRectangle(5,5,695,275,5,230,230,230);
					$Test->drawGraphArea(255,255,255,TRUE);
					$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,45,2);
					$Test->drawGrid(4,TRUE,230,230,230,50);
					
					// Draw the 0 line
					$Test->setFontProperties("graph/Fonts/tahoma.ttf",6);
					$Test->drawTreshold(0,143,55,72,TRUE,TRUE);
					
					// Draw the cubic curve graph
					$Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,50);
					
					// Finish the graph
					$Test->setFontProperties("graph/Fonts/tahoma.ttf",10);
					$Test->drawTitle(50,22, $q['series'],50,50,50,585);
					$Test->Render("render/" . $chartname);
				}   
			}
		}		
		
		return $imagehashes;
		
	}
	
}

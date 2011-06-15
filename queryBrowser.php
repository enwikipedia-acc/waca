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

class QueryBrowser
{
	
	var $numberedList = false;
	var $numberedListTitle = "#";
	var $tableCallbackFunction = false;
	var $overrideTableTitles = false;
	
	public function executeQuery($query)
	{
		global $tsSQL;
		$results = $tsSQL->query($query);
		return $results;
	}
	
	public function executeQueryToTable($query)
	{
		$out = "";

		
		$results = $this->executeQuery($query);

	
		$out.= '<table class="sortable" cellspacing="0"><tr>';

		if($this->numberedList == true)
		{
			$out.="<th>" . $this->numberedListTitle . "</th>";
		}

		if($this->overrideTableTitles != false)
		{
			foreach($this->overrideTableTitles as $value)
			{
				$out.=  "<th>" . $value . "</th>"; 
			}
		}
		else
		{
			for ($i = 0; $i < mysql_num_fields($results) ; $i++)
			{
				$out.=  "<th>" . mysql_field_name($results,$i) . "</th>"; 
			}	
		}
		$out.=  "</tr>";
		

		
		$currentreq = 0;
		while($row = mysql_fetch_assoc($results))
		{
			$currentreq++;
			if(function_exists($this->tableCallbackFunction))
			{
				$out .= call_user_func($this->tableCallbackFunction, $row, $currentreq);	
			}
			else
			{
				$out.=  '<tr';
				if ($currentreq % 2 == 0) {
					$out.=  ' class="alternate">';
				} else {
					$out.=  '>';
				}
	
				if($this->numberedList == true)
				{
					$out.="<th>" . $currentreq . "</th>";
				}
				
				
				foreach ($row as $cell) {
	
					$out.=  "<td>" . $cell . "</td>";
				}
	
				
				$out.="</tr>";
			}
			
		}
		

		
		$out.=  "</table>";
		

		return $out;
	}
	
	public function executeQueryToArray($query)
	{
		$resultset = $this->executeQuery($query);
		
		$results = array();
		
		while($row = mysql_fetch_assoc($resultset))
		{
			$results[] = $row;
		}
		
		return $results;
	}
	
}
?>
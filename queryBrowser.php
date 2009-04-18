<?php

class QueryBrowser
{
	
	var $numberedList = false;
	var $numberedListTitle = "#";
	
	public function executeQuery($query)
	{
		global $tsSQLlink,$asSQLlink ,$toolserver_database;
		list($tsSQLlink, $asSQLlink) = getDBconnections();
		@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting TS database.");

		$results = mysql_query($query,$tsSQLlink) or sqlerror(mysql_error(),"Ooops in QueryBrowser");
	
		return $results;
	}
	
	public function executeQueryToTable($query)
	{
		$out = "";

		
		$results = $this->executeQuery($query);

	
		$out.= '<table cellspacing="0"><tr>';

		if($this->numberedList == true)
		{
			$out.="<th>" . $this->numberedListTitle . "</th>";
		}

		for ($i = 0; $i < mysql_num_fields($results) ; $i++)
		{
			$out.=  "<th>" . mysql_field_name($results,$i) . "</th>"; 
		}
		
		$out.=  "</tr>";
		

		
		$currentreq = 0;
		while($row = mysql_fetch_assoc($results))
		{

			
			$currentreq++;
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

<?php

class QueryBrowser
{
	
	public function executeQueryToTable($query)
	{
		$out = "";
		

		
		global $tsSQLlink,$asSQLlink ,$toolserver_database;
		list($tsSQLlink, $asSQLlink) = getDBconnections();
		@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting TS database.");

		$results = mysql_query($query,$tsSQLlink) or sqlerror(mysql_error(),"Ooops in QueryBrowser");

	
		$out.= '<table cellspacing="0"><tr>';


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

			
			
			foreach ($row as $cell) {

				$out.=  "<td>" . $cell . "</td>";
			}

			
			$out.="</tr>";
			
		}
		

		
		$out.=  "</table>";
		

		return $out;
	}
	
}

?>

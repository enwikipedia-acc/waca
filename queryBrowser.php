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
	/**
	 * @var boolean
	 */
	public $numberedList = false;

	/**
	 * @var string
	 */
	public $numberedListTitle = "#";

	/**
	 * @var boolean|callable
	 */
	public $tableCallbackFunction = false;

	/**
	 * @var boolean|string[]
	 */
	public $overrideTableTitles = false;

	/**
	 * @var int
	 */
	public $rowFetchMode = PDO::FETCH_ASSOC;

	/**
	 * @param string $query
	 * @return string
	 */
	public function executeQueryToTable($query)
	{
		$out = "";

		$results = $this->executeQueryToArray($query);

		$out .= '<table class="table table-striped table-hover table-condensed"><tr>';

		if ($this->numberedList == true) {
			$out .= "<th>" . $this->numberedListTitle . "</th>";
		}

		if ($this->overrideTableTitles != false) {
			foreach ($this->overrideTableTitles as $value) {
				$out .= "<th>" . $value . "</th>"; 
			}
		}
		else {
			if (count($results) > 0) {
				foreach ($results[0] as $k => $v) {
					$out .= "<th>" . $k . "</th>"; 
				}
			}
		}
		$out .= "</tr>";
		
		
		$currentreq = 0;
		foreach ($results as $row) {
			$currentreq++;
			if (function_exists($this->tableCallbackFunction)) {
				$out .= call_user_func($this->tableCallbackFunction, $row, $currentreq);	
			}
			else {
				$out .= '<tr>';
                
				if ($this->numberedList == true) {
					$out .= "<th>" . $currentreq . "</th>";
				}
				
				
				foreach ($row as $cell) {
	
					$out .= "<td>" . $cell . "</td>";
				}
	
				
				$out .= "</tr>";
			}
			
		}
		
		$out .= "</table>";
		
		return $out;
	}
	
	public function executeQueryToArray($query)
	{
		$database = gGetDb();
        
		$statement = $database->prepare($query);
        
		$statement->execute();
        
		return $statement->fetchAll($this->rowFetchMode);
	}
	
}

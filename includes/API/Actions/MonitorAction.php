<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\API\Actions;

use DOMElement;
use DateTime;
use Waca\API\IApiAction;
use Waca\PdoDatabase;
use Waca\Tasks\ApiPageBase;

/**
 * MonitorAction short summary.
 *
 * MonitorAction description.
 *
 * @version 1.0
 * @author  stwalkerster
 */
class MonitorAction extends ApiPageBase implements IApiAction
{
	/**
	 * The database
	 * @var PdoDatabase $database
	 */
	private $database;

	public function executeApiAction(DOMElement $apiDocument)
	{
		$this->database = $this->getDatabase();

		$now = new DateTime();

		$old = $this->getOldest();
		$oldest = new DateTime($old);

		$new = $this->getNewest();
		$newest = new DateTime($new);

		$monitoringElement = $this->document->createElement("data");
		$monitoringElement->setAttribute("date", $now->format('c'));
		$monitoringElement->setAttribute("oldest", $old === null ? null : $oldest->format('c'));
		$monitoringElement->setAttribute("newest", $new === null ? null : $newest->format('c'));
		$apiDocument->appendChild($monitoringElement);

		return $apiDocument;
	}

	/**
	 * @return string|null
	 */
	private function getOldest()
	{
		$statement = $this->database->prepare("SELECT min(date) FROM request WHERE email != :email AND ip != :ip;");
		$successful = $statement->execute(array(
			':email' => $this->getSiteConfiguration()->getDataClearEmail(),
			':ip'    => $this->getSiteConfiguration()->getDataClearIp(),
		));

		if (!$successful) {
			return null;
		}

		$result = $statement->fetchColumn();

		return $result;
	}

	/**
	 * @return string
	 */
	private function getNewest()
	{
		$statement = $this->database->prepare("SELECT max(date) FROM request WHERE email != :email AND ip != :ip;");
		$statement->execute(array(
			':email' => $this->getSiteConfiguration()->getDataClearEmail(),
			':ip'    => $this->getSiteConfiguration()->getDataClearIp(),
		));

		$result = $statement->fetchColumn(0);

		return $result;
	}
}

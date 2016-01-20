<?php

namespace Waca\API\Actions;

use Waca\API\ApiActionBase as ApiActionBase;
use Waca\API\IApiAction as IApiAction;

use \PdoDatabase as PdoDatabase;

/**
 * MonitorAction short summary.
 *
 * MonitorAction description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class MonitorAction extends ApiActionBase implements IApiAction
{
	/**
	 * The database
	 * @var PdoDatabase $database
	 */
	private $database;

	public function execute(\DOMElement $apiDocument)
	{
		$this->database = gGetDb();

		$now = new \DateTime();

		$old = $this->getOldest();
		$oldest = new \DateTime($old);

		$new = $this->getNewest();
		$newest = new \DateTime($new);

		$monitoringElement = $this->document->createElement("data");
		$monitoringElement->setAttribute("date", $now->format('c'));
		$monitoringElement->setAttribute("oldest", $old == null ? null : $oldest->format('c'));
		$monitoringElement->setAttribute("newest", $new == null ? null : $newest->format('c'));
		$apiDocument->appendChild($monitoringElement);

		return $apiDocument;
	}

	/**
	 * @return string|null
	 */
	private function getOldest()
	{
		global $cDataClearIp, $cDataClearEmail;
		$statement = $this->database->prepare("select min(date) from request where email != :email and ip != :ip;");
		$successful = $statement->execute(array(':email' => $cDataClearEmail, ':ip' => $cDataClearIp));

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
		global $cDataClearIp, $cDataClearEmail;
		$statement = $this->database->prepare("select max(date) from request where email != :email and ip != :ip;");
		$statement->execute(array(':email' => $cDataClearEmail, ':ip' => $cDataClearIp));
		$result = $statement->fetchColumn(0);
		return $result;
	}
}

<?php

namespace Waca\API\Actions;

use Waca\API\ApiActionBase as ApiActionBase;
use Waca\API\IApiAction as IApiAction;

use \PdoDatabase as PdoDatabase;

/**
 * API Count action
 */
class StatusAction extends ApiActionBase implements IApiAction
{
	/**
	 * The database
	 * @var PdoDatabase $database
	 */
	private $database;

	public function execute(\DOMElement $apiDocument)
	{
		$this->database = gGetDb();

		$statusElement = $this->document->createElement("status");
		$apiDocument->appendChild($statusElement);

		$query = $this->database->prepare(<<<SQL
            SELECT /* Api/StatusAction */ COUNT(*) AS count
            FROM request
            WHERE
                status = :pstatus
                AND emailconfirm = 'Confirmed';
SQL
		);

		global $availableRequestStates;
		foreach ($availableRequestStates as $key => $value) {
			$query->bindValue(":pstatus", $key);
			$query->execute();
			$sus = $query->fetchColumn();
			$statusElement->setAttribute($value['api'], $sus);
			$query->closeCursor();
		}

		$query = $this->database->prepare(<<<SQL
            SELECT /* Api/StatusAction */ COUNT(*) AS count
            FROM ban
            WHERE
                (duration > UNIX_TIMESTAMP() OR duration = -1)
                AND active = 1;
SQL
		);

		$query->execute();
		$sus = $query->fetchColumn();
		$statusElement->setAttribute("bans", $sus);
		$query->closeCursor();

		$query = $this->database->prepare("SELECT /* Api/StatusAction */ COUNT(*) AS count FROM user WHERE status = :ulevel;");
		$query->bindValue(":ulevel", "Admin");
		$query->execute();
		$sus = $query->fetchColumn();
		$statusElement->setAttribute("useradmin", $sus);
		$query->closeCursor();

		$query->bindValue(":ulevel", "User");
		$query->execute();
		$sus = $query->fetchColumn();
		$statusElement->setAttribute("user", $sus);
		$query->closeCursor();

		$query->bindValue(":ulevel", "New");
		$query->execute();
		$sus = $query->fetchColumn();
		$statusElement->setAttribute("usernew", $sus);
		$query->closeCursor();

		return $apiDocument;
	}
}

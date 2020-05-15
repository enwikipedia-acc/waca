<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\API\Actions;

use DOMElement;
use Waca\API\IXmlApiAction;
use Waca\Tasks\XmlApiPageBase;

/**
 * API Count action
 */
class StatusAction extends XmlApiPageBase implements IXmlApiAction
{
    public function executeApiAction(DOMElement $apiDocument)
    {
        $statusElement = $this->document->createElement("status");
        $apiDocument->appendChild($statusElement);

        $query = $this->getDatabase()->prepare(<<<SQL
            SELECT /* Api/StatusAction */ COUNT(*) AS count
            FROM request
            WHERE
                status = :pstatus
                AND emailconfirm = 'Confirmed';
SQL
        );

        $availableRequestStates = $this->getSiteConfiguration()->getRequestStates();

        foreach ($availableRequestStates as $key => $value) {
            $query->bindValue(":pstatus", $key);
            $query->execute();
            $sus = $query->fetchColumn();
            $statusElement->setAttribute($value['api'], $sus);
            $query->closeCursor();
        }

        $query = $this->getDatabase()->prepare(<<<SQL
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

        $query = $this->getDatabase()->prepare(<<<SQL
SELECT /* Api/StatusAction */ COUNT(*) AS count
FROM user WHERE status = :ulevel;
SQL
        );
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

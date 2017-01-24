<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\API\Actions;

use DOMElement;
use Waca\API\ApiException as ApiException;
use Waca\API\IApiAction as IApiAction;
use Waca\DataObjects\User;
use Waca\Tasks\ApiPageBase;
use Waca\WebRequest;

/**
 * API Count action
 */
class CountAction extends ApiPageBase implements IApiAction
{
    /**
     * The target user
     * @var User $user
     */
    private $user;

    public function executeApiAction(DOMElement $apiDocument)
    {
        $username = WebRequest::getString('user');
        if ($username === null) {
            throw new ApiException("Please specify a username");
        }

        $userElement = $this->document->createElement("user");
        $userElement->setAttribute("name", $username);
        $apiDocument->appendChild($userElement);

        $user = User::getByUsername($username, $this->getDatabase());

        if ($user === false) {
            $userElement->setAttribute("missing", "true");

            return $apiDocument;
        }

        $this->user = $user;

        $userElement->setAttribute("level", $this->user->getStatus());
        $userElement->setAttribute("created", $this->getAccountsCreated());

        $userElement->setAttribute("today", $this->getToday());

        // Let the IRC bot handle the result of this.
        $this->fetchAdminData($userElement);

        return $apiDocument;
    }

    private function getAccountsCreated()
    {
        $query = <<<QUERY
        SELECT COUNT(*) AS count
        FROM log
            LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
            INNER JOIN user ON log.user = user.id
        WHERE
            (oncreated = '1' OR log.action = 'Closed custom-y')
            AND log.objecttype = 'Request'
            AND user.username = :username;
QUERY;

        $statement = $this->getDatabase()->prepare($query);
        $statement->execute(array(":username" => $this->user->getUsername()));
        $result = $statement->fetchColumn();
        $statement->closeCursor();

        return $result;
    }

    private function getToday()
    {
        $query = <<<QUERY
        SELECT
            COUNT(*) AS count
        FROM log
            LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
            INNER JOIN user ON log.user = user.id
        WHERE
            log.timestamp LIKE :date
            AND (oncreated = '1' OR log.action = 'Closed custom-y')
            AND user.username = :username;
QUERY;

        $statement = $this->getDatabase()->prepare($query);
        $statement->bindValue(":username", $this->user->getUsername());
        $statement->bindValue(":date", date('Y-m-d') . "%");
        $statement->execute();
        $today = $statement->fetchColumn();
        $statement->closeCursor();

        return $today;
    }

    private function fetchAdminData(DOMElement $userElement)
    {
        $query = "SELECT COUNT(*) AS count FROM log WHERE log.user = :userid AND log.action = :action;";

        $statement = $this->getDatabase()->prepare($query);
        $statement->bindValue(":userid", $this->user->getId());
        $statement->bindValue(":action", "Suspended");
        $statement->execute();
        $sus = $statement->fetchColumn();
        $userElement->setAttribute("suspended", $sus);
        $statement->closeCursor();

        $statement->bindValue(":action", "Promoted");
        $statement->execute();
        $pro = $statement->fetchColumn();
        $userElement->setAttribute("promoted", $pro);
        $statement->closeCursor();

        $statement->bindValue(":action", "Approved");
        $statement->execute();
        $app = $statement->fetchColumn();
        $userElement->setAttribute("approved", $app);
        $statement->closeCursor();

        $statement->bindValue(":action", "Demoted");
        $statement->execute();
        $dem = $statement->fetchColumn();
        $userElement->setAttribute("demoted", $dem);
        $statement->closeCursor();

        $statement->bindValue(":action", "Declined");
        $statement->execute();
        $dec = $statement->fetchColumn();
        $userElement->setAttribute("declined", $dec);
        $statement->closeCursor();

        $statement->bindValue(":action", "Renamed");
        $statement->execute();
        $rnc = $statement->fetchColumn();
        $userElement->setAttribute("renamed", $rnc);
        $statement->closeCursor();

        $statement->bindValue(":action", "Edited");
        $statement->execute();
        $mec = $statement->fetchColumn();
        $userElement->setAttribute("edited", $mec);
        $statement->closeCursor();

        $statement->bindValue(":action", "Prefchange");
        $statement->execute();
        $pcc = $statement->fetchColumn();
        $userElement->setAttribute("prefchange", $pcc);
        $statement->closeCursor();

        // Combine all three actions affecting Welcome templates into one count.
        $combinedquery = $this->getDatabase()->prepare(<<<SQL
            SELECT
                COUNT(*) AS count
            FROM log
            WHERE log.user = :userid
                AND log.action IN ('CreatedTemplate', 'EditedTemplate', 'DeletedTemplate');
SQL
        );

        $combinedquery->bindValue(":userid", $this->user->getId());
        $combinedquery->execute();
        $dtc = $combinedquery->fetchColumn();
        $userElement->setAttribute("welctempchange", $dtc);
        $combinedquery->closeCursor();

        // Combine both actions affecting Email templates into one count.
        $combinedquery = $this->getDatabase()->prepare(<<<SQL
            SELECT COUNT(*) AS count
            FROM log
            WHERE log.user = :userid
                AND log.action IN ('CreatedEmail', 'EditedEmail');
SQL
        );

        $combinedquery->bindValue(":userid", $this->user->getId());
        $combinedquery->execute();
        $cec = $combinedquery->fetchColumn();
        $userElement->setAttribute("emailtempchange", $cec);
        $combinedquery->closeCursor();
    }
}

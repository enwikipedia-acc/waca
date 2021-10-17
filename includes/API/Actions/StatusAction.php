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
use Waca\DataObjects\RequestQueue;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\RequestStatus;
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
                AND queue = :queue
                AND emailconfirm = 'Confirmed';
SQL
        );

        $allQueues = RequestQueue::getAllQueues($this->getDatabase());

        foreach ($allQueues as $value) {
            $query->bindValue(":pstatus", RequestStatus::OPEN);
            $query->bindValue(":queue", $value->getId());
            $query->execute();
            $sus = $query->fetchColumn();
            $statusElement->setAttribute($value->getApiName(), $sus);
            $query->closeCursor();
        }

        // hospital queue
        $search = RequestSearchHelper::get($this->getDatabase())->isHospitalised();

        if ($this->getSiteConfiguration()->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }
        $search->getRecordCount($hospitalCount);
        $statusElement->setAttribute('x-hospital', $hospitalCount);

        // job queue
        $search = RequestSearchHelper::get($this->getDatabase())
            ->byStatus(RequestStatus::JOBQUEUE);

        if ($this->getSiteConfiguration()->getEmailConfirmationEnabled()) {
            $search->withConfirmedEmail();
        }

        $search->getRecordCount($jobQueueRequestCount);
        $statusElement->setAttribute('x-jobqueue', $jobQueueRequestCount);

        // bans
        $query = $this->getDatabase()->prepare(<<<SQL
            SELECT /* Api/StatusAction */ COUNT(*) AS count
            FROM ban
            WHERE
                (duration > UNIX_TIMESTAMP() OR duration is null)
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

        $query->bindValue(":ulevel", "New");
        $query->execute();
        $sus = $query->fetchColumn();
        $statusElement->setAttribute("usernew", $sus);
        $query->closeCursor();

        $query = $this->getDatabase()->prepare(<<<SQL
select /* Api/StatusAction */ COUNT(*) from user u
inner join userrole ur on u.id = ur.user
where u.status = 'Active' and ur.role = :ulevel
SQL
        );

        $query->bindValue(":ulevel", "admin");
        $query->execute();
        $sus = $query->fetchColumn();
        $statusElement->setAttribute("useradmin", $sus);
        $query->closeCursor();

        $query->bindValue(":ulevel", "user");
        $query->execute();
        $sus = $query->fetchColumn();
        $statusElement->setAttribute("user", $sus);
        $query->closeCursor();

        return $apiDocument;
    }
}

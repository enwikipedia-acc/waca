<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\SearchHelpers;

use DateTime;
use PDO;
use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\PdoDatabase;

class UserSearchHelper extends SearchHelperBase
{
    /**
     * UserSearchHelper constructor.
     *
     * @param PdoDatabase $database
     */
    public function __construct(PdoDatabase $database)
    {
        parent::__construct($database, 'user', User::class);
    }

    /**
     * Initiates a search for requests
     *
     * @param PdoDatabase $database
     *
     * @return UserSearchHelper
     */
    public static function get(PdoDatabase $database)
    {
        $helper = new UserSearchHelper($database);

        return $helper;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function byStatus($status)
    {
        $this->whereClause .= ' AND status = ?';
        $this->parameterList[] = $status;

        return $this;
    }

    public function statusIn($statuses)
    {
        $this->inClause('status', $statuses);

        return $this;
    }

    public function byRole(string $role, Domain $domain) : UserSearchHelper
    {
        $this->joinClause .= ' INNER JOIN userrole r on origin.id = r.user';
        $this->whereClause .= ' AND r.domain = ? AND r.role = ?';
        $this->parameterList[] = $domain->getId();
        $this->parameterList[] = $role;

        return $this;
    }

    /**
     * @param DateTime $instant
     *
     * @return $this
     */
    public function lastActiveBefore(DateTime $instant)
    {
        $this->whereClause .= ' AND origin.lastactive < ? AND approvaldate.timestamp < ?';
        $this->joinClause .= <<<'SQLFRAG'
 LEFT JOIN (
    SELECT objectid, MAX(timestamp) timestamp 
    FROM log
    WHERE objecttype = 'User' AND action = 'Approved' 
    GROUP BY objectid
    ) approvaldate ON approvaldate.objectid = origin.id
SQLFRAG;
        $formattedDate = $instant->format("Y-m-d H:i:s");
        $this->parameterList[] = $formattedDate;
        $this->parameterList[] = $formattedDate;

        return $this;
    }

    public function getRoleMap(&$roleMap, Domain $domain)
    {
        $query = <<<SQL
            SELECT /* UserSearchHelper/roleMap */ 
                  r.user user
                , group_concat(r.role SEPARATOR ', ') roles 
            FROM userrole r 
            WHERE user IN ({$this->buildQuery(array('id'))})
            AND r.domain = ?
            GROUP BY r.user
SQL;

        $statement = $this->database->prepare($query);
        $statement->execute(array_merge($this->parameterList, [$domain->getId()]));

        $roleMap = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $roleMap[$row['user']] = $row['roles'];
        }

        return $this;
    }

    public function withReservedRequest()
    {
        $this->joinClause = ' INNER JOIN request req ON req.reserved = origin.id';
        $this->groupByClause = ' GROUP BY origin.id, origin.username';

        return $this->fetchMap('username');
    }
}

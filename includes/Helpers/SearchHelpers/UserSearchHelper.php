<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\SearchHelpers;

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

    /**
     * @param string $role
     *
     * @return $this
     */
    public function byRole($role)
    {
        $this->joinClause .= ' INNER JOIN userrole r on origin.id = r.user';
        $this->whereClause .= ' AND r.role = ?';
        $this->parameterList[] = $role;

        return $this;
    }
}

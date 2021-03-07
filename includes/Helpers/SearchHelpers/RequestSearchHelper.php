<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\SearchHelpers;

use Waca\DataObjects\Request;
use Waca\PdoDatabase;
use Waca\RequestStatus;
use Waca\SiteConfiguration;

class RequestSearchHelper extends SearchHelperBase
{
    /**
     * RequestSearchHelper constructor.
     *
     * @param PdoDatabase $database
     */
    protected function __construct(PdoDatabase $database)
    {
        parent::__construct($database, 'request', Request::class);
    }

    /**
     * Initiates a search for requests
     *
     * @param PdoDatabase $database
     *
     * @return RequestSearchHelper
     */
    public static function get(PdoDatabase $database)
    {
        $helper = new RequestSearchHelper($database);

        return $helper;
    }

    /**
     * Filters the results by IP address
     *
     * @param string $ipAddress
     *
     * @return $this
     */
    public function byIp($ipAddress)
    {
        $this->whereClause .= ' AND (ip LIKE ? OR forwardedip LIKE ?)';
        $this->parameterList[] = $ipAddress;
        $this->parameterList[] = '%' . trim($ipAddress, '%') . '%';

        return $this;
    }

    /**
     * Filters the results by email address
     *
     * @param string $emailAddress
     *
     * @return $this
     */
    public function byEmailAddress($emailAddress)
    {
        $this->whereClause .= ' AND email LIKE ?';
        $this->parameterList[] = $emailAddress;

        return $this;
    }

    /**
     * Filters the results by name
     *
     * @param string $name
     *
     * @return $this
     */
    public function byName($name)
    {
        $this->whereClause .= ' AND name LIKE ?';
        $this->parameterList[] = $name;

        return $this;
    }

    /**
     * Filters the requests to those with a defined status
     *
     * @param $status
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
     * Excludes a request from the results
     *
     * @param int $requestId
     *
     * @return $this
     */
    public function excludingRequest($requestId)
    {
        $this->whereClause .= ' AND id <> ?';
        $this->parameterList[] = $requestId;

        return $this;
    }

    /**
     * Filters the results to only those with a confirmed email address
     *
     * @return $this
     */
    public function withConfirmedEmail()
    {
        $this->whereClause .= ' AND emailconfirm = ?';
        $this->parameterList[] = 'Confirmed';

        return $this;
    }

    /**
     * Filters the results to exclude purged data
     *
     * @param SiteConfiguration $configuration
     *
     * @return $this
     */
    public function excludingPurgedData(SiteConfiguration $configuration)
    {
        $this->whereClause .= ' AND ip <> ? AND email <> ?';
        $this->parameterList[] = $configuration->getDataClearIp();
        $this->parameterList[] = $configuration->getDataClearEmail();

        return $this;
    }

    /**
     * Filters the requests to those without a defined status
     *
     * @param $status
     *
     * @return $this
     */
    public function excludingStatus($status)
    {
        $this->whereClause .= ' AND status <> ?';
        $this->parameterList[] = $status;

        return $this;
    }

    /**
     * Filters the requests to those which have failed an auto-creation
     *
     * @return $this
     */
    public function isHospitalised()
    {
        $this->whereClause .= ' AND status = ?';
        $this->parameterList[] = RequestStatus::HOSPITAL;

        return $this;
    }

    /**
     * Filters the requests to those which have not failed an auto-creation
     *
     * @return $this
     */
    public function notHospitalised()
    {
        $this->whereClause .= ' AND status <> ?';
        $this->parameterList[] = RequestStatus::HOSPITAL;

        return $this;
    }

    public function fetchByStatus($statuses)
    {
        return $this->fetchByParameter(' AND status = ?', $statuses);
    }
}

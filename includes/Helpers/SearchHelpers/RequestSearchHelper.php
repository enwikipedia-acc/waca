<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
     * @param int|null    $domain
     *
     * @return RequestSearchHelper
     */
    public static function get(PdoDatabase $database, ?int $domain)
    {
        $helper = new RequestSearchHelper($database);

        if ($domain !== null) {
            $helper->whereClause .= ' AND domain = ?';
            $helper->parameterList[] = $domain;
        }

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
     * Filters the results by comment
     *
     * @param string $comment
     *
     * @return $this
     */
    public function byComment($comment)
    {
        $this->modifiersClause = 'DISTINCT';
        $this->joinClause .= ' INNER JOIN comment c ON origin.id = c.request';
        $this->whereClause .= ' AND c.comment LIKE ?';
        $this->parameterList[] = $comment;

        return $this;
    }

    /**
     * Filters the results by comment security
     *
     * @param array $security List of allowed values for the security clause
     *
     * @return $this
     */
    public function byCommentSecurity(array $security)
    {
        $this->inClause('c.visibility', $security);

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

    public function fetchByQueue($queues)
    {
        return $this->fetchByParameter(' AND queue = ?', $queues);
    }
}

<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca;

/**
 * Class RequestList
 *
 * This class is used as a wrapper around a list of requests for display on the main page, the expanded request list,
 * and the search page.
 */
class RequestList
{
    public $requests;
    public $showPrivateData;
    public $dataClearEmail;
    public $dataClearIp;
    public $relatedEmailRequests;
    public $relatedIpRequests;
    public $requestTrustedIp;
    public $canBan;
    public $canBreakReservation;
    public $userList;
    public $commonEmail;
}

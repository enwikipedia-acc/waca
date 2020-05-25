<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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
}

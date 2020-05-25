<!-- requestlist.tpl -->
{if $requests.total > $requestLimitShowOnly}
    <div class="alert alert-warning alert-accordion">
        <strong>Miser mode:</strong>
        Not all requests are shown for speed. Only {$requestLimitShowOnly} of {$requests.total} are shown here.
        <a class="btn btn-sm btn-outline-secondary" href="{$baseurl}/internal.php/requestList?status={$requests.type|escape:'url'}">
            Show all {$requests.total} requests
        </a>
    </div>
{/if}
{if $requests.total > 0}
    {include file="mainpage/requesttable.tpl" requests=$requests.requests relatedIpRequests=$requests.relatedIpRequests relatedEmailRequests=$requests.relatedEmailRequests requestTrustedIp=$requests.requestTrustedIp}
{else}
    <em>No requests at this time</em>
{/if}

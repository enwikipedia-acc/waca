<!-- requestlist.tpl -->
{if $requests.total > $requestLimitShowOnly}
    <div class="alert alert-error">
        <h4>Miser mode:</h4>
        <p>
            Not all requests are shown for speed. Only {$requestLimitShowOnly} of {$requests.total} are shown here.
            <a class="btn btn-sm" href="{$baseurl}/internal.php/requestList?status={$requests.type|escape:'url'}">
                Show all {$requests.total} requests
            </a>
        </p>
    </div>
{/if}
{if count($requests.requests) > 0}
    {include file="mainpage/requesttable.tpl" requests=$requests.requests userlist=$requests.userlist}
{else}
    <em>No requests at this time</em>
{/if}
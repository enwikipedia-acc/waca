{extends file="pagebase.tpl"}
{block name="content"}

    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">List {$header|escape|lower} requests</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            {if $queuehelp}
                <p>{$queuehelp}</p>
            {/if}
            <p>There {if $totalRequests !== 1}are {$totalRequests} requests{else}is 1 request{/if} open in this queue.</p>
            {if $totalRequests > $requestLimitShowOnly}<p>Not all of these requests will show on the main page, as the main page is limited to showing {$requestLimitShowOnly} requests.</p>{/if}
        </div>
    </div>

    <div class="row">
        <div class="col-12">
        {if count($totalRequests) > 0}
            {include file="mainpage/requesttable.tpl" showStatus=false}
        {else}
            <em>No requests at this time</em>
        {/if}
        </div>
    </div>
{/block}

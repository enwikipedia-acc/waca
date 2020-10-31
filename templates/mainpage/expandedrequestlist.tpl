{extends file="pagebase.tpl"}
{block name="content"}

    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{$header|escape}: All requests</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            {if $queuehelp}
                <p class="prewrap">{$queuehelp}</p>
            {/if}
            <p>There {if $totalRequests !== 1}are {$totalRequests} requests{else}is 1 request{/if} open in this queue.</p>
            {if $totalRequests > $requestLimitShowOnly}<p>Not all of these requests will show on the main page, as the main page is limited to showing {$requestLimitShowOnly} requests.</p>{/if}
        </div>
    </div>

    <div class="row">
        <div class="col-12">
        {if count($totalRequests) > 0}
            {include file="mainpage/requesttable.tpl" showStatus=false list=$requests}
        {else}
            <span class="font-italic text-muted">No requests at this time</span>
        {/if}
        </div>
    </div>
{/block}

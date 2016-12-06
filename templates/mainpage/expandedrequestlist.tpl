{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row-fluid">
        <div class="page-header">
            <h1>{$header|escape} <span class="badge {if $totalRequests > $requestLimitShowOnly}badge-important{else}badge-info{/if}">{if $totalRequests > 0}{$totalRequests}{/if}</span></h1>
        </div>
    </div>
    <div class="row-fluid">
        {if count($totalRequests) > 0}
            {include file="mainpage/requesttable.tpl" showStatus=false}
        {else}
            <em>No requests at this time</em>
        {/if}
    </div>
{/block}
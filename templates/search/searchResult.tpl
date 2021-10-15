{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Search <small class="text-muted">for a request</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <p class="lead">Searching for "{$term|escape}" as {$target}...</p>
            {if $resultCount == 0}
                {include file="alert.tpl" alertblock=false alerttype="alert-info" alertclosable=false alertheader='' alertmessage='No requests found!'}
            {else}
                {include file="mainpage/requesttable.tpl" showStatus=true list=$requests sort=$defaultSort dir=$defaultSortDirection}
            {/if}
        </div>
    </div>
{/block}

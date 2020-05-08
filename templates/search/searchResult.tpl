{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <h1>Search<small class="text-muted"> for a request</small></h1>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-md-12">
            <p class="lead">Searching for "{$term|escape}" as {$target}...</p>
            {if count($requests) == 0}
                {include file="alert.tpl" alertblock=false alerttype="alert-info" alertclosable=false alertheader='' alertmessage='No requests found!'}
            {else}
                {include file="mainpage/requesttable.tpl" showStatus=true userlist=$userlist}
            {/if}
        </div>
    </div>
{/block}

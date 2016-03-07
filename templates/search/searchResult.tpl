{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Search
            <small> for a request</small>
        </h1>
    </div>

    <h4>Searching for "{$term|escape}" as {$target}...</h4>
    {if count($requests) == 0}
        {include file="alert.tpl" alertblock=false alerttype="alert-info" alertclosable=false alertheader='' alertmessage='No requests found!'}
    {else}
        {include file="mainpage/requesttable.tpl" showStatus=true userlist=$userlist}
    {/if}
{/block}
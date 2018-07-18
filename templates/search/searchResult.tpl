{extends file="pagebase.tpl"}
{block name="content"}
    <div class="jumbotron">
        <h1>Search</h1>
        <p> for a request</p>
    </div>

    <h4>Searching for "{$term|escape}" as {$target}...</h4>
    {if count($requests) == 0}
        {include file="alert.tpl" alertblock=false alerttype="alert-info" alertclosable=false alertheader='' alertmessage='No requests found!'}
    {else}
        {include file="mainpage/requesttable.tpl" showStatus=true userlist=$userlist}
    {/if}
{/block}

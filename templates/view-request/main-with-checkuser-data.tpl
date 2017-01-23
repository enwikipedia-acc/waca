{extends file="view-request/main-with-data.tpl"}

{block name="requestDataPrimaryCheckUser"}
    {if $canSeeCheckuserData}
        <div class="row-fluid">
            <div class="span4"><strong>User Agent:</strong></div>
            <div class="span8">{$requestUserAgent|escape}</div>
        </div>
    {/if}
{/block}

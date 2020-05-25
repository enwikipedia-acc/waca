{extends file="view-request/main-with-data.tpl"}

{block name="requestDataPrimaryCheckUser"}
    {if $canSeeCheckuserData}
        <div class="row">
            <div class="col-md-4"><strong>User Agent:</strong></div>
            <div class="col-md-8">
                {if !$requestDataCleared}
                    {$requestUserAgent|escape}
                {else}
                    <span class="text-muted font-italic">User agent purged</span>
                {/if}
            </div>
        </div>
    {/if}
{/block}

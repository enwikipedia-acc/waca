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

        <div class="row">
            <div class="col-md-4"><strong>Client Hints:</strong></div>
            <div class="col-md-8">
                {if count($requestClientHints) > 0}
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#client-hint-container" aria-expanded="false">Show Client Hint data</button>
                {else}
                    <span class="text-muted font-italic">Client hints purged</span>
                {/if}
            </div>
        </div>

        {if count($requestClientHints) > 0}
            <div class="row collapse" id="client-hint-container">
                <div class="col">
                    {foreach $requestClientHints as $ch}
                        <div class="row client-hint">
                            <div class="client-hint-name"><strong>{$ch->getName()|escape}</strong></div>
                            <div class="client-hint-value">
                                {$ch->getValue()|escape}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        {/if}
    {/if}
{/block}

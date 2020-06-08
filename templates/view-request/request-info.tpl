<div class="request-data-section">
    <div class="row">
        <div class="col-md-4"><strong>Requested name:</strong></div>
        <div class="col-md-8">{$requestName|escape}</div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <strong>Date:</strong>
        </div>
        <div class="col-md-8">
            {$requestDate|date} <span class="text-muted">({$requestDate|relativedate})</span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <strong>Status:</strong>
        </div>
        <div class="col-md-8">
            {$requestStatus|escape}
        </div>
    </div>

    {block name="requestDataPrimary"}<!-- Request data not available in this template -->{/block}

    {block name="requestDataPrimaryCheckUser"}<!-- Request data not available in this template -->{/block}

    <div class="row">
        <div class="col-md-4">
            <strong>Reserved by:</strong>
        </div>
        <div class="col-md-8">
            {if $requestIsReserved}
                {$requestReservedByName|escape}
                {if $showRevealLink}
                    (
                    <a href="{$baseurl}/internal.php/viewRequest?id={$requestId}&amp;hash={$revealHash}">reveal to others</a>
                    )
                {/if}
            {else}
                None
            {/if}
        </div>
    </div>
</div>

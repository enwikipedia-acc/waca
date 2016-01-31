<div class="row-fluid">
    <div class="span4"><strong>Requested name:</strong></div>
    <div class="span8">{$requestName|escape}</div>
</div>

<div class="row-fluid">
    <div class="span4">
        <strong>Date:</strong>
    </div>
    <div class="span8">
        {$requestDate} <span class="muted">({$requestDate|relativedate})</span>
    </div>
</div>

{block name="requestDataPrimary"}<!-- Request data not available in this template -->{/block}

{block name="requestDataPrimaryCheckUser"}<!-- Request data not available in this template -->{/block}

<div class="row-fluid">
    <div class="span4">
        <strong>Reserved by:</strong>
    </div>
    <div class="span8">
        {if $requestIsReserved}
            {$requestReservedByName|escape}
            {block name="requestDataRevealLink"}{/block}
        {else}
            None
        {/if}
    </div>
</div>
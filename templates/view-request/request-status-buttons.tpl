<div class="row-fluid">
    <h5 class="zoom-button-header">Request status:</h5>
</div>

<div class="row-fluid">
    {if $requestIsReservedByMe && !$requestIsClosed}
        {block name="declinedeferbuttons"}{/block}
    {/if}

    <div class="span6{if (!$requestIsReserved) || $requestIsClosed } offset6{/if}">
        {if $requestIsClosed}
            <div class="span12">
                <form action="{$baseurl}/internal.php/viewRequest/defer" method="post" class="form-compact">
                    {include file="security/csrf.tpl"}
                    <input type="hidden" name="request" value="{$requestId}"/>
                    <input type="hidden" name="updateversion" value="{$updateVersion}"/>
                    <input type="hidden" name="target" value="{$defaultRequestState}"/>
                    <button class="btn btn-block" type="submit">Reset request</button>
                </form>
            </div>
        {else}
            {if $requestIsReservedByMe || (!$requestIsReserved)}
                {include file="view-request/defer-button.tpl"}
                <div class="span6">
                    <form method="post" action="{$baseurl}/internal.php/viewRequest/drop" class="form-compact">
                        {include file="security/csrf.tpl"}
                        <button class="btn btn-inverse btn-block" type="submit" name="template" value="0">
                            Drop
                        </button>
                        <input type="hidden" name="updateversion" value="{$updateVersion}"/>
                        <input type="hidden" name="request" value="{$requestId}"/>
                    </form>
                </div>
            {/if}
        {/if}
    </div>

</div>
<hr class="zoom-button-divider"/>
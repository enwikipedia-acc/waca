<div class="row-fluid">
    <h5 class="zoom-button-header">Request status:</h5>
</div>

<div class="row-fluid">
    {if $requestIsReserved}
        {include file="view-request/created-button.tpl"}
        <div class="span4">
            {include file="view-request/decline-button.tpl"}{include file="view-request/custom-button.tpl"}
        </div>
        <!-- /span4 -->
    {/if}

    <div class="span4{if ! $requestIsReserved} offset8{/if}">
        {if $requestIsClosed}
            <div class="span12">
                <form action="{$baseurl}/internal.php/viewRequest/defer" method="post" class="form-compact">
                    <input type="hidden" name="request" value="{$requestId}"/>
                    <input type="hidden" name="target" value="{$defaultRequestState}"/>
                    <button class="btn btn-block" type="submit">Reset request</button>
                </form>
            </div>
        {else}
            {include file="view-request/defer-button.tpl"}
            <div class="span6">
                <form method="post" action="{$baseurl}/internal.php/viewRequest/drop" class="form-compact">
                    <button class="btn btn-inverse btn-block" type="submit" name="template" value="0">
                        Drop
                    </button>
                    <input type="hidden" name="request" value="{$requestId}"/>
                </form>
            </div>
        {/if}
    </div>

</div>
<hr class="zoom-button-divider"/>
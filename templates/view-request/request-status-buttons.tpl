<div class="row-fluid">
    <h5 class="zoom-button-header">Request status:</h5>
</div>

<div class="row-fluid">
    {if $requestIsReserved}
        {include file="view-request/created-button.tpl"}
        <div class="span4">
            {include file="view-request/decline-button.tpl"}{*{include file="view-request/custom-button.tpl"}*}
        </div>
        <!-- /span4 -->
    {/if}

    <div class="span4{if ! $requestIsReserved} offset8{/if}">
        {if $requestIsClosed}
            <form action="{$baseurl}/internal.php/viewRequest/defer" method="post">
                <input type="hidden" name="request" value="{$requestId}"/>
                <input type="hidden" name="target" value="{$defaultRequestState}"/>
                <button class="btn span12" type="submit">Reset request</button>
            </form>
        {else}
            {include file="view-request/defer-button.tpl"}
          {*  <a class="btn btn-inverse span6"
               href="{$baseurl}/acc.php?action=done&amp;id={$requestId}&amp;email=0">
                Drop
            </a>*}
        {/if}
    </div>

</div>
<hr class="zoom-button-divider" />
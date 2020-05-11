<h5 class="zoom-button-header">Request status:</h5>

<div class="row">
    {if $requestIsReserved}
        {include file="view-request/created-button.tpl"}
        <div class="col-md-4">
            <div class="row">
                {include file="view-request/decline-button.tpl"}{include file="view-request/custom-button.tpl"}
            </div>
        </div>
    {/if}

    <div class="col-md-4{if ! $requestIsReserved} offset-md-8{/if}">
        <div class="row">
            {if $requestIsClosed}
                <div class="col-md-12">
                    <form action="{$baseurl}/internal.php/viewRequest/defer" method="post">
                        {include file="security/csrf.tpl"}
                        <input type="hidden" name="request" value="{$requestId}"/>
                        <input type="hidden" name="updateversion" value="{$updateVersion}"/>
                        <input type="hidden" name="target" value="{$defaultRequestState}"/>
                        <button class="btn btn-block btn-outline-danger" type="submit">Reset request</button>
                    </form>
                </div>
            {else}
                {include file="view-request/defer-button.tpl"}
                <div class="col-md-6">
                    <form method="post" action="{$baseurl}/internal.php/viewRequest/drop">
                        {include file="security/csrf.tpl"}
                        <button class="btn btn-dark btn-block" type="submit" name="template" value="0">
                            Drop
                        </button>
                        <input type="hidden" name="request" value="{$requestId}"/>
                    </form>
                </div>
            {/if}
        </div>
    </div>

</div>
<hr class="zoom-button-divider"/>

<h5>Request status</h5>

<div class="row">
    {if $requestIsReservedByMe && !$requestIsClosed}
        {block name="declinedeferbuttons"}{/block}
    {/if}

    <div class="col-md-6{if $requestIsClosed || ! $requestIsReserved } offset-md-6{/if}">
        <div class="row">
            {if $requestIsClosed}
                {if $requestDataCleared && !$canResetPurgedRequest}
                    <div class="col-md-12">
                        <button class="btn btn-outline-danger btn-block disabled" data-toggle="tooltip" data-placement="top" title="You are not allowed to re-open a request for which the private data has been purged">
                            Reset request
                        </button>
                    </div>
                {elseif $isOldRequest && !$canResetOldRequest}
                    <div class="col-md-12">
                        <button class="btn btn-outline-danger btn-block disabled" data-toggle="tooltip" data-placement="top" title="You are not allowed to re-open a request that has been closed for over a week">
                            Reset request
                        </button>
                    </div>
                {else}
                    <div class="col-md-12">
                        <form action="{$baseurl}/internal.php/viewRequest/defer" method="post">
                            {include file="security/csrf.tpl"}
                            <input type="hidden" name="request" value="{$requestId}"/>
                            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
                            <input type="hidden" name="target" value="{$defaultRequestState}"/>
                            {if $requestStatus === Waca\RequestStatus::JOBQUEUE}
                                <button class="btn btn-block btn-outline-danger" type="submit">Reset request and cancel auto-creation</button>
                            {else}
                                <button class="btn btn-block btn-outline-danger" type="submit">Reset request</button>
                            {/if}
                        </form>
                    </div>
                {/if}
            {else}
                {if $requestIsReservedByMe || (!$requestIsReserved)}
                    {include file="view-request/defer-button.tpl"}
                    <div class="col-md-6">
                        <form method="post" action="{$baseurl}/internal.php/viewRequest/drop">
                            {include file="security/csrf.tpl"}
                            <button class="btn btn-dark btn-block jsconfirm" type="submit" name="template" value="0">
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

</div>
<hr/>

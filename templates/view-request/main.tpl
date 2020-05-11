{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">View request details <small class="text-muted">for request #{$requestId}</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- request details -->
        <div class="col-lg-6">
            {include file="view-request/request-info.tpl"}
            <hr class="zoom-button-divider" />
            {block name="createButton"}{/block}

            {include file="view-request/reservation-section.tpl"}

            {block name="requestStatusButtons"}{/block}

            {block name="banSection"}{/block}
        </div>
        <div class="col-lg-6">
            {include file="view-request/request-log.tpl"}
        </div>
    </div><!--/row-->

    {include file="view-request/username-section.tpl"}

    {block name="ipSection"}{/block}

    {block name="emailSection"}{/block}

    {block name="otherRequests"}
        <div class="row">
            <div class="col-md-6">
                <h3>Other requests from this email address</h3>
                {if $requestDataCleared}
                    <p class="text-muted">Email information cleared</p>
                {else}
                    <p class="text-muted">Data currently not visible.</p>
                {/if}
            </div>
            <div class="col-md-6">
                <h3>Other requests from this IP address</h3>
                {if $requestDataCleared}
                    <p class="text-muted">IP information cleared</p>
                {else}
                    <p class="text-muted">Data currently not visible.</p>
                {/if}
            </div>
        </div>
    {/block}
{/block}

{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Create an account! <small class="text-muted">All request queues</small></h1>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {if count($requestSectionData) > 0}
            <div class="accordion" id="requestListAccordion">
                {foreach from=$requestSectionData key="header" item="section"}
                    {include file="mainpage/accordiongroup.tpl"}
                {/foreach}
            </div>
            {else}
                <div class="alert alert-warning">
                    <h5>Oh!</h5>
                    <p>It looks like there's no groups of requests that you are able to see right now.</p>
                </div>
            {/if}
        </div>
    </div>

    {if $showLastFive}
        <hr/>
        {include file="mainpage/lastFive.tpl"}
    {/if}
{/block}

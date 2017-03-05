{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row-fluid">
        <div class="page-header">
            <h1>Create an account!</h1>
        </div>
    </div>
    {if count($requestSectionData) > 0}
        <div class="row-fluid">
            <div class="accordion" id="requestListAccordion">
                {foreach from=$requestSectionData key="header" item="section"}
                    {include file="mainpage/accordiongroup.tpl"}
                {/foreach}
            </div>
        </div>
    {else}
        <div class="alert alert-warning">
            <h5>Oh!</h5>
            <p>It looks like there's no groups of requests that you are able to see right now.</p>
        </div>
    {/if}
    {if $showLastFive}
        <hr/>
        {include file="mainpage/lastFive.tpl"}
    {/if}
{/block}
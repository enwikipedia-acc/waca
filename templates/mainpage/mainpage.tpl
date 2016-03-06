{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row-fluid">
        <div class="page-header">
            <h1>Create an account!</h1>
        </div>
    </div>
    <div class="row-fluid">

        <div class="accordion" id="requestListAccordion">
            {foreach from=$requestSectionData key="header" item="section"}
                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#requestListAccordion"
                           href="#collapse{$section.api}">
                            {$header|escape} <span class="badge badge-info">{if $section.total > 0}{$section.total}{/if}</span>
                        </a>
                    </div>
                    <div id="collapse{$section.api|escape}" class="accordion-body collapse out">
                        <div class="accordion-inner">
                            {include file="mainpage/requestlist.tpl" requests=$section showStatus=false}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
    <hr/>
    <div class="row-fluid">
        <h3>Last 5 Closed requests</h3>
        <table class="table table-condensed table-striped" style="width:auto;">
            <thead>
            <th>ID</th>
            <th>Name</th>
            <th>{* zoom *}</th>
            </thead>
            {foreach from=$lastFive item="req"}
                <tr>
                    <th>{$req.id}</th>
                    <td>
                        {$req.name|escape}
                    </td>
                    <td>
                        <a href="{$baseurl}/internal.php/viewRequest?id={$req.id|escape:'url'}" class="btn btn-info">
                            <i class="icon-white icon-search"></i>&nbsp;Zoom
                        </a>
                    </td>
                    <td>
                        <form action="{$baseurl}/internal.php/viewRequest/defer" method="post" class="form-compact">
                            <input type="hidden" name="request" value="{$req.id}"/>
                            <input type="hidden" name="updateversion" value="{$req.updateversion}"/>
                            <input type="hidden" name="target" value="{$defaultRequestState}"/>
                            <button class="btn btn-warning" type="submit">
                                <i class="icon-white icon-refresh"></i>&nbsp;Reset
                            </button>
                        </form>
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
{/block}
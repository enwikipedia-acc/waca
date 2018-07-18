{extends file="pagebase.tpl"}
{block name="content"}
    <div class="jumbotron">
        <h1>Create an account!</h1>
    </div>
    <div class="accordion" id="requestListAccordion">
        {foreach from=$requestSectionData key="header" item="section"}
            <div class="card">
                <div class="card-header">
                    <button class="btn btn-link" data-toggle="collapse" data-parent="#requestListAccordion"
                    data-target="#collapse{$section.api}">
                        {$header|escape} <span class="badge {if $section.total > $requestLimitShowOnly}badge-important{else}badge-info{/if}">{if $section.total > 0}{$section.total}{/if}</span>
                    </button>
                </div>
                <div id="collapse{$section.api|escape}" class="collapse out">
                    <div class="card-body">
                        {include file="mainpage/requestlist.tpl" requests=$section showStatus=false}
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
    <hr/>
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
                        <i class="fas fa-search"></i>&nbsp;Zoom
                    </a>
                </td>
                <td>
                    <form action="{$baseurl}/internal.php/viewRequest/defer" method="post" class="form-row">
                        {include file="security/csrf.tpl"}
                        <input class="form-control" type="hidden" name="request" value="{$req.id}"/>
                        <input class="form-control" type="hidden" name="updateversion" value="{$req.updateversion}"/>
                        <input class="form-control" type="hidden" name="target" value="{$defaultRequestState}"/>
                        <button class="btn btn-warning" type="submit">
                            <i class="fas fa-sync"></i>&nbsp;Reset
                        </button>
                    </form>
                </td>
            </tr>
        {/foreach}
    </table>
{/block}

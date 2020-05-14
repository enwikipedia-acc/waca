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
            <div class="accordion" id="requestListAccordion">
                {foreach from=$requestSectionData key="header" item="section"}
                    <div class="card overflow-visible">
                        <div class="card-header position-relative py-0">
                            <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#requestListAccordion" data-target="#collapse{$section.api|escape}">
                                {$header|escape} <span class="badge {if $section.total > $requestLimitShowOnly}badge-danger{else}badge-info{/if} badge-pill">{if $section.total > 0}{$section.total}{/if}</span>
                            </button>
                        </div>
                        <div id="collapse{$section.api|escape}" class="collapse out" data-parent="#requestListAccordion">
                            <div class="card-body">
                                {include file="mainpage/requestlist.tpl" requests=$section showStatus=false}
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-md-12">
            <h4>Last 5 Closed requests</h4>
            <table class="table table-sm table-striped table-nonfluid table-sm">
                <thead>
                    <th>ID</th>
                    <th>Name</th>
                    <th>{* zoom *}</th>
                    <th>{* reset *}</th>
                </thead>
                {foreach from=$lastFive item="req"}
                    <tr>
                        <th>{$req.id}</th>
                        <td>
                            {$req.name|escape}
                        </td>
                        <td>
                            <a href="{$baseurl}/internal.php/viewRequest?id={$req.id|escape:'url'}" class="btn btn-info btn-sm">
                                <i class="fas fa-search"></i>&nbsp;Zoom
                            </a>
                        </td>
                        <td>
                            <form action="{$baseurl}/internal.php/viewRequest/defer" method="post" class="form-row">
                                {include file="security/csrf.tpl"}
                                <input class="form-control" type="hidden" name="request" value="{$req.id}"/>
                                <input class="form-control" type="hidden" name="updateversion" value="{$req.updateversion}"/>
                                <input class="form-control" type="hidden" name="target" value="{$defaultRequestState}"/>
                                <button class="btn btn-warning btn-sm" type="submit">
                                    <i class="fas fa-sync"></i>&nbsp;Reset
                                </button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
{/block}

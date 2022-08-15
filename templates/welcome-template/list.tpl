{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Welcome templates</h1>
                {if $canAdd}
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a class="btn btn-sm btn-outline-success" href="{$baseurl}/internal.php/welcomeTemplates/add"><i class="fas fa-plus"></i>&nbsp;Add new Welcome Template</a>
                </div>
                {/if}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p class="text-muted">
                This page allows you to choose a template to use to automatically welcome the users you create. Use the
                Select button to choose the template you wish to use. If the template you want to use is not on the
                list, please ask a tool admin to add it for you.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped table-hover table-nonfluid sortable">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th><span class="d-none d-xl-inline">Code</span></th>
                        {if $canEdit}
                            <th class="d-none d-lg-block text-nowrap">Used by:</th>
                        {/if}
                        <th data-defaultsort="disabled"><!-- Buttons --></th>
                    </tr>
                </thead>
                {if $canSelect}
                    <tfoot>
                        <tr {if $currentTemplate == null}class="table-success"{/if}>
                            <th colspan="2">Disable automatic welcoming</th>
                            {if $canEdit}
                                <th class="d-none d-lg-block"><!-- count --></th>
                            {/if}
                            <td class="table-button-cell">
                                {if $currentTemplate !== null}
                                    <form method="post" action="{$baseurl}/internal.php/welcomeTemplates/select">
                                        {include file="security/csrf.tpl"}
                                        <input type="hidden" name="disable" value="true"/>
                                        <button type="submit" class="btn btn-primary btn-sm float-right">
                                            <i class="fas fa-check"></i>&nbsp;Select
                                        </button>
                                    </form>
                                {else}
                                    <a href="" class="btn btn-primary btn-sm disabled float-right">Selected</a>
                                {/if}
                            </td>
                        </tr>
                    </tfoot>
                {/if}
                <tbody>
                {foreach from=$templateList item="t" name="templateloop"}
                    <tr {if $currentTemplate == $t->getId()}class="table-success"{/if}>
                        <td>
                            {$t->getUserCode()|escape}
                        </td>
                        <td>
                            <code class="d-none d-xl-inline">{$t->getBotCode()|escape}</code>
                        </td>
                        {if $canEdit}
                            <td class="table-button-cell d-none d-lg-table-cell" data-value="{count($t->getUsersUsingTemplate())}">
                                <a class="btn {if count($t->getUsersUsingTemplate()) > 0}btn-warning{else}btn-primary disabled{/if} btn-sm"
                                   {if count($t->getUsersUsingTemplate()) > 0}rel="popover"{/if} href="#"
                                   title="Users using this template" id="#tpl{$t->getId()}"
                                   data-content="{{include file="linkeduserlist.tpl" users=$t->getUsersUsingTemplate()}|escape}"
                                   data-html="true">
                                    {count($t->getUsersUsingTemplate())}
                                </a>
                            </td>
                        {/if}
                        <td class="table-button-cell">
                            <a href="{$baseurl}/internal.php/welcomeTemplates/view?template={$t->getId()}" class="btn btn-outline-primary btn-sm {if $canEdit}d-none d-md-inline-block{/if}">
                                <i class="fas fa-eye"></i><span class="d-none d-md-inline">&nbsp;View</span>
                            </a>
                            {if $canEdit}
                                <a href="{$baseurl}/internal.php/welcomeTemplates/edit?template={$t->getId()}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i><span class="d-none d-md-inline">&nbsp;Edit</span>
                                </a>

                                <form method="post" action="{$baseurl}/internal.php/welcomeTemplates/delete" class=" d-inline">
                                    {include file="security/csrf.tpl"}
                                    <input type="hidden" name="template" value="{$t->getId()}"/>
                                    <input type="hidden" name="updateversion" value="{$t->getUpdateVersion()}"/>
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i><span class="d-none d-md-inline">&nbsp;Delete</span>
                                    </button>
                                </form>
                            {/if}
                            {if $currentTemplate != $t->getId()}
                                {if $canSelect}
                                    <form method="post" action="{$baseurl}/internal.php/welcomeTemplates/select" class="d-inline-block">
                                        {include file="security/csrf.tpl"}
                                        <input type="hidden" name="template" value="{$t->getId()}"/>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-check"></i><span class="{if $canEdit}d-none d-sm-inline{/if}">&nbsp;Select</span>
                                        </button>
                                    </form>
                                {/if}
                            {else}
                                <a href="" class="btn btn-primary btn-sm disabled">Selected</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/block}

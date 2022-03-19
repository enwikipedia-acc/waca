{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Request Form Management <small class="text-muted">Create and edit request forms</small></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    {if $canCreate}
                        <a class="btn btn-sm btn-outline-success" href="{$baseurl}/internal.php/requestFormManagement/create"><i class="fas fa-plus"></i>&nbsp;Create new request form</a>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th></th>
                        <th>Public endpoint</th>
                        <th>Override queue</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$forms item=form }
                        <tr>
                            <th class="text-nowrap">
                                {$form->getName()|escape}
                            </th>
                            <td>
                                {if $form->isEnabled()}
                                    <span class="badge badge-success">Enabled</span>
                                {else}
                                    <span class="badge badge-danger">Disabled</span>
                                {/if}
                            </td>
                            <td class="text-nowrap">
                                <a href="{$baseurl}/index.php/r/{$form->getDomainObject()->getShortName()|escape}/{$form->getPublicEndpoint()|escape}">{$baseurl}/index.php/r/{$form->getDomainObject()->getShortName()|escape}/{$form->getPublicEndpoint()|escape}</a>
                            </td>
                            <td>
                                {if isset($queues[$form->getOverrideQueue()])}
                                    {$queues[$form->getOverrideQueue()]->getHeader()|escape}
                                {/if}
                            </td>
                            <td class="table-button-cell">
                                {if $canView}
                                    <a class="btn btn-outline-primary btn-sm" href="{$baseurl}/internal.php/requestFormManagement/view?form={$form->getId()|escape}"><i class="fas fa-eye"></i>&nbsp;View</a>
                                {/if}
                                {if $canEdit}
                                    <a class="btn btn-warning btn-sm" href="{$baseurl}/internal.php/requestFormManagement/edit?form={$form->getId()|escape}"><i class="fas fa-pencil-alt"></i>&nbsp;Edit</a>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/block}

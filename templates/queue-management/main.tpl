{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Request Queue Management <small class="text-muted">Create and edit request queues</small></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    {if $canCreate}
                        <a class="btn btn-sm btn-outline-success" href="{$baseurl}/internal.php/queueManagement/create"><i class="fas fa-plus"></i>&nbsp;Create new queue</a>
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
                        <th>Queue header</th>
                        <th></th>
                        <th>Target display name</th>
                        <th>API name</th>
                        <th>Help text</th>
                        <th class="text-muted">Legacy log name</th>
                        {if $canEdit}
                            <th></th>
                        {/if}
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$queues item=queue }
                        <tr>
                            <th class="text-nowrap">
                                {$queue->getHeader()|escape}
                            </th>
                            <td>
                                {if $queue->isEnabled()}
                                    <span class="badge badge-success">Enabled</span>
                                {else}
                                    <span class="badge badge-danger">Disabled</span>
                                {/if}
                                {if $queue->isDefault()}
                                    <span class="badge badge-primary">Default queue</span>
                                {/if}
                                {if $queue->isDefaultAntispoof()}
                                    <span class="badge badge-info">AntiSpoof</span>
                                {/if}
                                {if $queue->isDefaultTitleBlacklist()}
                                    <span class="badge badge-info">Title Blacklist</span>
                                {/if}
                            </td>
                            <td class="text-nowrap">
                                {$queue->getDisplayName()|escape}
                            </td>
                            <td class="text-nowrap">
                                {$queue->getApiName()|escape}
                            </td>
                            <td class="prewrap">{$queue->getHelp()|escape}</td>
                            <td class="text-nowrap text-muted">
                                {$queue->getLogName()|escape}
                            </td>
                            {if $canEdit}
                                <td class="table-button-cell">
                                    <a class="btn btn-warning btn-sm" href="{$baseurl}/internal.php/queueManagement/edit?queue={$queue->getId()|escape}"><i class="fas fa-pencil-alt"></i>&nbsp;Edit</a>
                                </td>
                            {/if}
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/block}

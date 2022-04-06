{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Domain Management <small class="text-muted">Manage security domain settings</small></h1>
                {if $canCreate && $canEditAll}
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a class="btn btn-sm btn-outline-success" href="{$baseurl}/internal.php/domainManagement/create"><i class="fas fa-plus"></i>&nbsp;Create domain</a>
                    </div>
                {/if}
            </div>
        </div>
    </div>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
        {foreach from=$domains item=domain}
            <div class="col">
                <div class="card mb-4 ">
                    <div class="card-header {if $domain->isEnabled()}border-success{else}border-danger{/if} border-w2">
                        <h5 class="d-inline-block mb-0 mt-1">{$domain->getShortName()|escape}</h5>
                        {if ($currentDomain->getId() == $domain->getId() && $canEdit) || ($canEdit && $canEditAll)}
                            <a href="{$baseurl}/internal.php/domainManagement/edit?domain={$domain->getId()|escape}" class="btn btn-secondary btn-sm float-right ml-4">
                                <i class="fas fa-edit"></i>&nbsp; Edit
                            </a>
                        {/if}
                    </div>
                    <div class="card-body">
                        <p class="h5 d-inline-block float-right">
                            <span class="badge badge-{if $domain->isEnabled()}success{else}danger{/if}">{if $domain->isEnabled()}Enabled{else}Disabled{/if}</span>
                        </p>
                        <h4 class="card-title">{$domain->getLongName()|escape}</h4>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th>Article path</th><td><a href="{$domain->getWikiArticlePath()|escape}">{$domain->getWikiArticlePath()|escape}</a></td>
                            </tr>
                            {if ($currentDomain->getId() == $domain->getId() && $canEdit) || $canEditAll}
                            <tr>
                                <th>API</th><td><a href="{$domain->getWikiApiPath()|escape}">{$domain->getWikiApiPath()|escape}</a></td>
                            </tr>
                            {/if}
                            <tr>
                                <th>Language</th><td>{$domain->getDefaultLanguage()|escape}</td>
                            </tr>
                            <tr>
                                <th>Email sender</th><td>{$domain->getEmailSender()|escape}</td>
                            </tr>
                            {if ($currentDomain->getId() == $domain->getId() && $canEdit) || $canEditAll}
                                <tr>
                                    <th>Notification target</th><td>{$domain->getNotificationTarget()}</td>
                                </tr>
                            {/if}
                            {if $currentDomain->getId() == $domain->getId() || $canEditAll}
                                <tr>
                                    <th>Default creation close</th>
                                    <td>
                                    {if $domain->getDefaultClose() !== null}
                                        <a href="{$baseurl}/internal.php/emailManagement/view?id={$domain->getDefaultClose()}">{$closeTemplates[$domain->getDefaultClose()]->getName()}</a>
                                    {else}
                                        <span class="badge badge-secondary">Not set</span>
                                    {/if}
                                    </td>
                                </tr>
                            {/if}
                            <tr>
                                <th>Documentation</th><td><a href="{$domain->getLocalDocumentation()|escape}">{$domain->getLocalDocumentation()|escape}</a></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{/block}
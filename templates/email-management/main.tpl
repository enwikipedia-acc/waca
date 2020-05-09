{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Email Management <small class="text-muted">Create and edit close reasons</small></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    {if $canCreate}
                        <a class="btn btn-sm btn-outline-success" href="{$baseurl}/internal.php/emailManagement/create"><i class="fas fa-plus"></i>&nbsp;Create new Message</a>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <h3>Active Emails</h3>
            {include file="email-management/template-table.tpl" templates=$activeTemplates}
        </div>

        <div class="col-xl-6">
            <h3>Inactive Emails</h3>
            {include file="email-management/template-table.tpl" templates=$inactiveTemplates}
        </div>
    </div>
{/block}

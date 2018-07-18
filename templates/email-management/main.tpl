{extends file="pagebase.tpl"}
{block name="content"}
    <div class="jumbotron">
        <h1>Email Management</h1>
        <p>Create and edit close reasons</p>
        {if $canCreate}
            <a class="btn btn-primary" href="{$baseurl}/internal.php/emailManagement/create">
                <i class="fas fa-plus"></i>&nbsp;Create new Message
            </a>
        {/if}
    </div>
    <div class="row">
        <div class="col-lg-6">
            <h3>Active Emails</h3>
            {include file="email-management/template-table.tpl" templates=$activeTemplates}
        </div>

        <div class="col-lg-6">
            <h3>Inactive Emails</h3>
            {include file="email-management/template-table.tpl" templates=$inactiveTemplates}
        </div>
    </div>
{/block}

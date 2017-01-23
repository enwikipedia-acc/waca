{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Email Management
            <small>
                Create and edit close reasons
                {if $canCreate}
                    <a class="btn btn-primary" href="{$baseurl}/internal.php/emailManagement/create">
                        <i class="icon-white icon-plus"></i>&nbsp;Create new Message
                    </a>
                {/if}
            </small>
        </h1>
    </div>
    <div class="row-fluid">
        <div class="span6">
            <h3>Active Emails</h3>
            {include file="email-management/template-table.tpl" templates=$activeTemplates}
        </div>

        <div class="span6">
            <h3>Inactive Emails</h3>
            {include file="email-management/template-table.tpl" templates=$inactiveTemplates}
        </div>
    </div>
{/block}

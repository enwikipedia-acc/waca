{extends file="pagebase.tpl"}
{block name="content"}
    <!-- tpl:logs/main.tpl -->
    <div class="page-header">
        <h1>Log Viewer&nbsp;
            <small>See all the logs</small>
        </h1>
    </div>
    {include file="logs/form.tpl"}
    {include file="logs/pager.tpl"}
    {include file="logs/datatable.tpl" showComments=false}
    {include file="logs/pager.tpl"}
    <!-- /tpl:logs/main.tpl -->
{/block}
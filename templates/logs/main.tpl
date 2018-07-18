{extends file="pagebase.tpl"}
{block name="content"}
    <!-- tpl:logs/main.tpl -->
    <div class="jumbotron">
      <h1>Log Viewer&nbsp;</h1>
      <p>See all the logs</p>
    </div>
    {include file="logs/form.tpl"}
    {include file="logs/pager.tpl"}
    {include file="logs/datatable.tpl" showComments=false}
    {include file="logs/pager.tpl"}
    <!-- /tpl:logs/main.tpl -->
{/block}

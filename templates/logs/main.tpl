{extends file="pagebase.tpl"}
{block name="content"}
    <!-- tpl:logs/main.tpl -->
    <div class="row">
        <div class="col-md-12">
            <h1>Log Viewer <small class="text-muted">See all the logs</small></h1>
        </div>
    </div>
    <hr />

    <div class="row">
       <div class="col-md-12">
           {include file="logs/form.tpl"}
       </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {include file="logs/pager.tpl"}
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {include file="logs/datatable.tpl" showComments=false}
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {include file="logs/pager.tpl"}
        </div>
    </div>
    <!-- /tpl:logs/main.tpl -->
{/block}

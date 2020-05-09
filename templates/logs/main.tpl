{extends file="pagebase.tpl"}
{block name="content"}
    <!-- tpl:logs/main.tpl -->
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Log viewer <small class="text-muted">See all the logs</small></h1>
            </div>
        </div>
    </div>

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

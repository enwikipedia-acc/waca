{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Job Queue Management <small class="text-muted">All jobs</small></h1>
            </div>
        </div>
    </div>

    <form method="get">
        <div class="form-row">
            <div class="col-md-6 col-lg-3 col-xl-2">
                <label class="sr-only" for="inputUsername">Username</label>
                <input type="text" id="inputUsername" class="form-control username-typeahead"
                       placeholder="All users" name="filterUser" value="{$filterUser|escape}" />
            </div>
            <div class="col-md-6 col-lg-2 col-xl-2">
                <label class="sr-only" for="inputRequest">Request ID</label>
                <input type="number" id="inputRequest" class="form-control" placeholder="All requests"
                                                         name="filterRequest" value="{$filterRequest|escape}" />
            </div>

            <div class="col-md-6 col-lg-3 col-xl-2">
                <label class="sr-only" for="inputTask">Task type</label>
                <select id="inputTask" name="filterTask" title="Task" class="form-control">
                    <option value="">All tasks</option>
                    {foreach $taskNameMap as $task => $description}
                        <option value="{$task|escape}" {if $task == $filterTask}selected="selected"{/if}>{$description|escape}</option>
                    {/foreach}
                </select>
            </div>

            <div class="col-md-6 col-lg-3 col-xl-2">
                <label class="sr-only" for="inputStatus">Task status</label>

                <select id="inputStatus" name="filterStatus" title="Status" class="form-control">
                    <option value="">All statuses</option>
                    {foreach $statusDescriptionMap as $status => $description}
                        <option value="{$status|escape}" {if $status == $filterStatus}selected="selected"{/if}>{$status|escape}: {$description|escape}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="col-md-6 py-2">
                {foreach item=value from=[50,100,500]}
                    <div class="custom-control-inline custom-radio">
                        <input class="custom-control-input" type="radio" id="limit{$value}" name="limit" value="{$value}" {if $limit == $value}checked{/if} />
                        <label class="custom-control-label" for="limit{$value}">{$value} results</label>
                    </div>
                {/foreach}
            </div>
        </div>
        
        <div class="form-row">
            <div class="col-md-6 py-2">
                    <div class="custom-control-inline custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="order" name="order" value="old" {if $order == 'old'}checked{/if} />
                        <label class="custom-control-label" for="order">Oldest First</label>
                    </div>
            </div>
        </div>

        <div class="form-row">
            <div class="col-md-6 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-search"></i>&nbsp;Search
                </button>
            </div>
        </div>

    </form>
    {include file="pager.tpl"}
    {include file="jobqueue/jobtable.tpl"}
    {include file="pager.tpl"}
{/block}

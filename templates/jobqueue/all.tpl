{extends file="base.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Job Queue Management
            <small>All jobs</small>
        </h1>
    </div>
    <form class="form-inline" method="get">
        <input type="text" id="inputUsername" class="username-typeahead" placeholder="All users" data-provide="typeahead"
               data-items="10" name="filterUser" value="{$filterUser|escape}"/>

        <input type="number" id="inputRequest" placeholder="All requests"
               name="filterRequest" value="{$filterRequest|escape}"/>

        <select id="inputTask" name="filterTask" title="Task">
            <option value="">All tasks</option>
            {foreach $taskNameMap as $task => $description}
                <option value="{$task|escape}" {if $task == $filterTask}selected="selected"{/if}>{$description|escape}</option>
            {/foreach}
        </select>

        <select id="inputStatus" name="filterStatus" title="Status">
            <option value="">All statuses</option>
            {foreach $statusDescriptionMap as $status => $description}
                <option value="{$status|escape}" {if $status == $filterStatus}selected="selected"{/if}>{$status|escape}: {$description|escape}</option>
            {/foreach}
        </select>

        <label class="radio inline">
            <input type="radio" id="inlineCheckbox1" name="limit" value="50" {if $limit == 50}checked{/if} /> 50 results
        </label>
        <label class="radio inline">
            <input type="radio" id="inlineCheckbox2" name="limit" value="100" {if $limit == 100}checked{/if} /> 100 results
        </label>
        <label class="radio inline">
            <input type="radio" id="inlineCheckbox3" name="limit" value="500" {if $limit == 500}checked{/if} /> 500 results
        </label>
        <button type="submit" class="btn btn-primary">
            <i class="icon-search icon-white"></i>&nbsp;Search
        </button>
    </form>
    {include file="pager.tpl"}
    {include file="jobqueue/jobtable.tpl"}
    {include file="pager.tpl"}
{/block}
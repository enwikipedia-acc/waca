<!-- tpl: logs/form.tpl -->
<form class="form-inline" method="get">
    <input type="text" id="inputUsername" class="username-typeahead" placeholder="All users" data-provide="typeahead"
           data-items="10" name="filterUser" value="{$filterUser|escape}"/>

    <select id="inputAction" name="filterAction" title="Log Action">
        <option value="">All log actions</option>
        {foreach $allLogActions as $action => $description}
            <option value="{$action|escape}" {if $action == $filterAction}selected="selected"{/if}>{$description|escape}</option>
        {/foreach}
    </select>
    <select id="inputObjectType" name="filterObjectType" title="Object type">
        <option value="">All object types</option>
        {foreach $allObjectTypes as $objectType => $description}
            <option value="{$objectType}" {if $objectType == $filterObjectType}selected="selected"{/if}>{$description}</option>
        {/foreach}
    </select>

    <input type="number" id="inputObjectId" placeholder="Object ID" name="filterObjectId" value="{$filterObjectId|escape}"/>

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
<!-- /tpl: logs/form.tpl -->

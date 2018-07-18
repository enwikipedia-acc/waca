<!-- tpl: logs/form.tpl -->
<form class="justify-content-center" method="get">
  <div class="form-row">
    <input type="text" id="inputUsername" class="form-control col-lg-3" placeholder="All users" data-provide="typeahead"
           data-items="10" name="filterUser" value="{$filterUser|escape}"/>
    <select class="form-control col-lg-3" id="inputAction" name="filterAction" title="Log Action">
        <option value="">All log actions</option>
        {foreach $allLogActions as $action => $description}
            <option value="{$action}" {if $action == $filterAction}selected="selected"{/if}>{$description}</option>
        {/foreach}
    </select>
    <select class="form-control col-lg-3" id="inputObjectType" name="filterObjectType" title="Object type">
        <option value="">All object types</option>
        {foreach $allObjectTypes as $objectType => $description}
            <option value="{$objectType}" {if $objectType == $filterObjectType}selected="selected"{/if}>{$description}</option>
        {/foreach}
    </select>
    <input class="form-control col-lg-3" type="number" id="inputObjectId" placeholder="Object ID" name="filterObjectId" value="{$filterObjectId|escape}"/>
  </div>
  <div class="form-row">
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" id="inlineCheckbox1" name="limit" value="50" {if $limit == 50}checked{/if} />
        <label class="form-check-label" for="inlineCheckbox1">50 results</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" id="inlineCheckbox2" name="limit" value="100" {if $limit == 100}checked{/if} />
        <label class="form-check-label" for="inlineCheckbox2">100 results</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" id="inlineCheckbox3" name="limit" value="500" {if $limit == 500}checked{/if} />
        <label class="form-check-label" for="inlineCheckbox1">500 results</label>
    </div>
  </div><div class="form-row">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i>&nbsp;Search
    </button>
  </div>
</form>
<!-- /tpl: logs/form.tpl -->

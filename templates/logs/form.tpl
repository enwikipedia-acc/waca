<!-- tpl: logs/form.tpl -->
<form method="get">
    <div class="form-row">
        <div class="col-lg-3 col-xl-2">
            <label class="sr-only" for="inputUsername">Username</label>
            <input type="text" id="inputUsername" class="form-control username-typeahead" placeholder="All users" name="filterUser" value="{$filterUser|escape}"/>
        </div>
        <div class="col-lg-3 col-xl-2">
            <label class="sr-only" for="inputAction">Log action</label>
            <select class="form-control" id="inputAction" name="filterAction" title="Log Action">
                <option value="">All log actions</option>
                {foreach $allLogActions as $section => $sectionActions}
                    <optgroup label="{$section}">
                        {foreach $sectionActions as $action => $description}
                            <option value="{$action|escape}" {if $action == $filterAction}selected="selected"{/if}>{$description|escape}</option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </div>
        <div class="col-lg-3 col-xl-2">
            <label class="sr-only" for="inputObjectType">Object Type</label>
            <select class="form-control" id="inputObjectType" name="filterObjectType" title="Object type">
                <option value="">All object types</option>
                {foreach $allObjectTypes as $objectType => $description}
                    <option value="{$objectType}" {if $objectType == $filterObjectType}selected="selected"{/if}>{$description}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-lg-3 col-xl-2">
            <label class="sr-only" for="inputObjectId">Object ID</label>
            <input class="form-control" type="number" id="inputObjectId" placeholder="Object ID" name="filterObjectId" value="{$filterObjectId|escape}"/>
        </div>
    </div>

    <div class="form-row">
        <div class="col-md-12 my-3">
            <div class="custom-control custom-radio custom-control-inline">
                <input class="custom-control-input" type="radio" id="inlineCheckbox1" name="limit" value="50" {if $limit == 50}checked{/if} />
                <label class="custom-control-label" for="inlineCheckbox1">50 results</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input class="custom-control-input" type="radio" id="inlineCheckbox2" name="limit" value="100" {if $limit == 100}checked{/if} />
                <label class="custom-control-label" for="inlineCheckbox2">100 results</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input class="custom-control-input" type="radio" id="inlineCheckbox3" name="limit" value="500" {if $limit == 500}checked{/if} />
                <label class="custom-control-label" for="inlineCheckbox3">500 results</label>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="col-xl-2 col-lg-3">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-search"></i>&nbsp;Search
            </button>
        </div>
    </div>
</form>
<!-- /tpl: logs/form.tpl -->

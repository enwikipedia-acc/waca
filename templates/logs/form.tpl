<!-- tpl: logs/form.tpl -->
<form class="form-inline" method="get" action="acc.php">
	<input type="hidden" name="action" value="logs" />
	<input type="hidden" name="page" value="1" />
  <input type="text" id="inputUsername" class="username-typeahead" placeholder="All users" data-provide="typeahead" data-items="10" name="filterUser" value="{$filterUser|escape}"/>
  <!--<input type="text" id="inputAction" placeholder="Filter by Action" name="filterAction" value="{$filterAction|escape}" />-->
	<select id="inputAction" name="filterAction">
		<option value="">All log actions</option>
		{foreach Logger::getLogActions() as $action => $description}
			<option value="{$action}" {if $action == $filterAction}selected="selected"{/if}>{$description}</option>
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
<!-- /tpl: logs/form.tpl -->

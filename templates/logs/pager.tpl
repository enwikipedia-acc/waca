<!-- tpl:logs/pager.tpl -->
<nav aria-label="Page navigation">
  <ul class="pagination justify-content-center">
		{assign searchParamsUrl "limit={$limit|escape}&amp;filterUser={$filterUser|escape}&amp;filterAction={$filterAction|escape}&amp;filterObjectType={$filterObjectType|escape}&amp;filterObjectId={$filterObjectId|escape}"}

    <li class="page-item {if $pagedata.canprev}{else}disabled{/if}">
			<a class="page-link" href="{if $pagedata.canprev}?page=1&amp;{$searchParamsUrl}{else}javascript: void(0){/if}">&laquo; First</a>
		</li>

    <li class="page-item {if $pagedata.canprev}{else}disabled{/if}">
			<a class="page-link" href="{if $pagedata.canprev}?page={$page - 1}&amp;{$searchParamsUrl}{else}javascript: void(0){/if}">&lsaquo; Prev</a>
		</li>

		{foreach from=$pagedata.pages item=pagenum}
			<li {if $pagenum == $page}class="page-item active"{/if}>
				<a class="page-link" href="?page={$pagenum}&amp;{$searchParamsUrl}">{$pagenum}</a>
			</li>
		{/foreach}

    <li class="page-item {if $pagedata.cannext}{else}disabled{/if}">
			<a class="page-link" href="{if $pagedata.cannext}?page={$page + 1}&amp;{$searchParamsUrl}{else}javascript: void(0){/if}">Next &rsaquo;</a>
		</li>

    <li class="page-item {if $pagedata.cannext}{else}disabled{/if}">
			<a class="page-link" href="{if $pagedata.cannext}?page={$pagedata.maxpage}&amp;{$searchParamsUrl}{else}javascript: void(0){/if}">Last &raquo;</a>
		</li>
  </ul>
</nav>
<!-- /tpl:logs/pager.tpl -->

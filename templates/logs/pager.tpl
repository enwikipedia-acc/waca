<!-- tpl:logs/pager.tpl -->
<div class="pagination">
  <ul>
		{assign searchParamsUrl "limit={$limit|escape}&amp;filterUser={$filterUser|escape}&amp;filterAction={$filterAction|escape}"}
		
    <li {if $pagedata.canprev}{else}class="disabled"{/if}>
			<a href="{if $pagedata.canprev}?action=logs&amp;page=1&amp;{$searchParamsUrl}{else}javascript: void(0){/if}">&laquo; First</a>
		</li>
		
    <li {if $pagedata.canprev}{else}class="disabled"{/if}>
			<a href="{if $pagedata.canprev}?action=logs&amp;page={$page - 1}&amp;{$searchParamsUrl}{else}javascript: void(0){/if}">&lsaquo; Prev</a>
		</li>
		
		{foreach from=$pagedata.pages item=pagenum}
			<li {if $pagenum == $page}class="active"{/if}>
				<a href="?action=logs&amp;page={$pagenum}&amp;{$searchParamsUrl}">{$pagenum}</a>
			</li>
		{/foreach}
		
    <li {if $pagedata.cannext}{else}class="disabled"{/if}>
			<a href="{if $pagedata.cannext}?action=logs&amp;page={$page + 1}&amp;{$searchParamsUrl}{else}javascript: void(0){/if}">Next &rsaquo;</a>
		</li>
		
    <li {if $pagedata.cannext}{else}class="disabled"{/if}>
			<a href="{if $pagedata.cannext}?action=logs&amp;page={$pagedata.maxpage}&amp;{$searchParamsUrl}{else}javascript: void(0){/if}">Last &raquo;</a>
		</li>
  </ul>
</div>
<!-- /tpl:logs/pager.tpl -->

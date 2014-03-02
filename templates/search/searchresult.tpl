<h4>Searching for "{$term|escape}" as {$target}...</h4>

{if count($requests) == 0}
	nothing!
{else}
	{include file="mainpage/requesttable.tpl" showStatus=true}
{/if}
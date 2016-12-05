<!-- requestlist.tpl -->
{if $requests.total > $requestLimitShowOnly}
	<div class="alert alert-error">
		<h4>Miser mode:</h4>
		Not all requests are shown for speed. Only {$requestLimitShowOnly} of {$requests.total} are shown here. <a class="btn btn-small" href="{$baseurl}/acc.php?action=listall&amp;status={$type|escape:'url'}">Show all {$requests.total} requests</a>
	</div>
{/if}
{if count($requests.requests) > 0}
	{include file="mainpage/requesttable.tpl" requests=$requests.requests}
{else}
	<em>No requests at this time</em>
{/if}
<!-- requestlist.tpl -->
{if $totalRequests > $requestLimitShowOnly}
	{include file="alert.tpl" alertblock="0" alerttype="alert-error" alertclosable="0" alertheader="Miser mode:"
		  alertmessage="Not all requests are shown for speed. Only {$requestLimitShowOnly} of {$totalRequests} are shown here."}
{/if}
{if count($requests) > 0}
	<table class="table table-striped sortable">
		<thead>
			<tr>
				<th data-defaultsort="asc"><span class="hidden-phone">#</span></th>
				<td><!-- zoom --></td>
				<td><!-- comment --></td>
				<th><span class="visible-desktop">Email address</span><span class="visible-tablet">Email and IP</span><span class="visible-phone">Request details</span></th>
				<th><span class="visible-desktop">IP address</span></th>
				<th><span class="hidden-phone">Username</span></th>
				<td><!-- ban --></td>
				<td><!-- reserve status --></td>
				<td><!--reserve button--></td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$requests item="r"}
				{include file="request-entry.tpl" request=$r}
			{/foreach}
		</tbody>
	</table>
{else}
	<em>No requests at this time</em>
{/if}
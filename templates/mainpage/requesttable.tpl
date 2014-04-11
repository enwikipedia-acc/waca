<table class="table table-striped sortable">
	<thead>
		<tr>
			<th data-defaultsort="asc"><span class="hidden-phone">#</span></th>
      {if $showStatus}
			  <th>Request state</th>
      {/if}
			<th>
        {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
          <span class="visible-desktop">Email address</span>
          <span class="visible-tablet">Email and IP</span>
          <span class="visible-phone">Request details</span>
        {else}
          <span class="visible-phone">Username</span>
        {/if}
      </th>
			<th>
        {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
          <span class="visible-desktop">IP address</span>
        {/if}
      </th>
			<th><span class="hidden-phone">Username</span></th>
			<th><span class="visible-desktop">Request time</span></th>
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

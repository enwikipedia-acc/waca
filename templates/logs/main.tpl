<!-- tpl:logs/main.tpl -->
<div class="page-header">
  <h1>Log Viewer&nbsp;<small>See all the logs</small></h1>
</div>

{include file="logs/form.tpl"}
{include file="logs/pager.tpl"}

<table class="table table-striped table-hover table-condensed table-nonfluid">
	<thead>
		<tr>
			<th>Timestamp</th>
			<th>User</th>
			<th>Action</th>
			<th>Object</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$logs item=entry name=logloop}
			<tr>
				<td>{$entry->getTimestamp()} <em class="muted">({$entry->getTimestamp()|relativedate})</em></td>
				<td>
					{if $entry->getUser() != -1}
						<a href='{$baseurl}/statistics.php?page=Users&amp;user={$entry->getUser()}'>
					{/if}
					{$entry->getUserObject()->getUsername()|escape}
					{if $entry->getUser() != -1}
						</a>
					{/if}
				</td>
				<td>{Logger::getLogDescription($entry)|escape}</td>
				<td>{$entry->getObjectDescription()}</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="logs/pager.tpl"}
<!-- /tpl:logs/main.tpl -->

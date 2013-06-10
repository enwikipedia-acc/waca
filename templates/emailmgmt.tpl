<h2>Email Management</h2>
{if $displayactive == true}
	<h3>Active Emails</h3>
	<table>
	{foreach $activeemails as $row}
		<tr><td>{$row@iteration}. </td><td>{$row.newmail_name}</td> <td><a class="btn btn-{if $userisadmin == true}warning{else}primary{/if}" href="{$tsurl}/acc.php?action=emailmgmt&amp;edit={$row.newmail_id}">{if $userisadmin == true}Edit!{else}View!{/if}</a></td></tr>
	{/foreach}
	</table>
{/if}
{if $displayinactive == true}
	<h3>Inactive Emails</h3>
	<table>
	{foreach $inactiveemails as $row}
		<tr><td>{$row@iteration}. </td><td>{$row.newmail_name}</td> <td><a class="btn btn-{if $userisadmin == true}warning{else}primary{/if}" href="{$tsurl}/acc.php?action=emailmgmt&amp;edit={$row.newmail_id}">{if $userisadmin == true}Edit!{else}View!{/if}</a></td></tr>
	{/foreach}
	</table>
{/if}

<h2>Email Management</h2>
<h3>Active Emails</h2>
<ol>
{foreach $activeemails as $row}
	<li>{$row.newmail_name}</li>
{/foreach}
</ol>

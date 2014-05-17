<div class="row-fluid">
	<div class="span6">
		<h2>Detail report for user: {$user->getUsername()|escape}</h2>
		<ul>
			<li>User ID: {$user->getId()}</li>
			<li>User Level: {$user->getStatus()}</li>
			<li>User On-wiki name: {$user->getOnWikiName()|escape} {if $user->isOAuthLinked()}<span class="label {if $user->getOnWikiName() == "##OAUTH##"}label-important{else}label-success{/if}">OAuth</span>{/if}</li>
			{if $user->getConfirmationDiff() != 0}<li><a href="{$mediawikiScriptPath}?diff={$user->getConfirmationDiff()}">Confirmation diff</a></li>{/if}
			<li>{if $user->getLastActive() == "0000-00-00 00:00:00"}User has never used the interface{else}User last active: {$user->getLastActive()}{/if}</li>
		</ul>

		{if $currentUser->isAdmin()}
			{include file="usermanagement/buttons.tpl"}
		{/if}
	</div>

	<div class="span6">
		<h2>Summary of user activity:</h2>
		<table class="table table-striped table-condensed">
		{foreach from=$activity item="row"}
			<tr><td>{$row.action}</td><td>{$row.count}</td></tr>
		{/foreach}
		</table>
	</div>
</div>

<div class="row-fluid">
	<div class="span6">
		<h2>Users created</h2>
		<ol>
		{foreach from=$created item="user"}
			<li>
				<a href="{$mediawikiScriptPath}?title=User:{$user.name|escape:'url'}">{$user.name|escape}</a> 
				( 
				<a href="{$mediawikiScriptPath}?title=User_talk:{$user.name|escape:'url'}">talk</a> - 
				<a href="{$mediawikiScriptPath}?title=Special:Contributions/{$user.name|escape:'url'}">contribs</a> - 
				<a href="{$baseurl}/acc.php?action=zoom&id={$user.id}">zoom</a>
				) at {$user.time}
			</li>
		{/foreach}
		</ol>
	</div>

	<div class="span6">
		<h2>Users not created</h2>
		<ol>
		{foreach from=$notcreated item="user"}
			<li>
				<a href="{$mediawikiScriptPath}?title=User:{$user.name|escape:'url'}">{$user.name|escape}</a> 
				( 
				<a href="{$mediawikiScriptPath}?title=User_talk:{$user.name|escape:'url'}">talk</a> - 
				<a href="{$mediawikiScriptPath}?title=Special:Contributions/{$user.name|escape:'url'}">contribs</a> - 
				<a href="{$baseurl}/acc.php?action=zoom&id={$user.id}">zoom</a>
				) at {$user.time}
			</li>
		{/foreach}
		</ol>
		
	</div>
</div>

<div class="row-fluid">
	<div class="span6">
		<h2>Account log</h2>
		<ol>
		{foreach from=$accountlog item="user"}
			<li>
				{$user.log_user} <strong>{$user.log_action}</strong> at {$user.log_time} {if $user.log_cmt == ""}{else}({$user.log_cmt}){/if}
			</li>
		{/foreach}
		</ol>
	</div>
</div>
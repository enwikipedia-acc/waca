{foreach from=$lists item="userlist" key="title"}
	<h3>{$title|escape}</h3>
	<ul>
		{foreach from=$userlist item="user"}
			<li><a href="?page=Users&amp;user={$user->getId()}">{$user->getUsername()|escape}</a></li>
		{/foreach}
	</ul>
{/foreach}
<h2>Edit comment #{$comment->getId()}</h2>
<form method="post">
    <strong>Time:</strong>&nbsp;{$comment->getTime()}<br />
	<strong>Author:</strong>&nbsp;{$comment->getUserObject()->getUsername()}<br />
	<strong>Security:</strong>&nbsp;<select name = "visibility">
	    <option value="user" {if $comment->getVisibility() == "user"}selected{/if}>User</option>
	    <option value="admin" {if $comment->getVisibility() == "admin"}selected{/if}>Admin</option>
    </select>
    <br />
	<strong>Request:</strong>&nbsp;<a href="{$tsurl}/acc.php?action=zoom&id={$comment->getRequest()}">#{$comment->getRequest()}</a>
    <br />
	<strong>Old text:</strong><pre>{$comment->getComment()|escape}</pre>
	<input type="text" size="100" name="newcomment" value="{$comment->getComment()|escape}" />
	<input type="submit" />
</form>
<div class="row-fluid">
    <h2>Edit comment #{$comment->getId()}</h2>
    <form method="post" class="form-horizontal">
        <div class="control-group">
            <label class="control-label" for="request">Request</label>
            <div class="controls">
              <span class="input-large uneditable-input"><a href="{$baseurl}/acc.php?action=zoom&id={$comment->getRequest()}">{$comment->getRequestObject()->getName()|escape}</a></span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="user">User</label>
            <div class="controls">
              <span class="input-large uneditable-input"><a href="{$baseurl}/statistics.php?page=Users&user={$comment->getUser()}">{$comment->getUserObject()->getUsername()|escape}</a></span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="time">Time</label>
            <div class="controls">
                <span class="input-large uneditable-input">{$comment->getTime()}</span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="visibility">Security</label>
            <div class="controls">
                <select name="visibility">
	                <option value="user" {if $comment->getVisibility() == "user"}selected{/if}>User</option>
	                <option value="admin" {if $comment->getVisibility() == "admin"}selected{/if}>Admin</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="oldtext">Old text</label>
            <div class="controls">
              <pre class="input-block span12">{$comment->getComment()|escape}</pre>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="newcomment">New text</label>
            <div class="controls">
              <input class="input-block span12" type="text" name="newcomment" id="newcomment" value="{$comment->getComment()|escape}" />
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</div>
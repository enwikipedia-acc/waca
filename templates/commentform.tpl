<div class="row-fluid">
    <h2>Comment on a request #{$request->getId()} <small>({$request->getName()|escape})</small></h2>
    <form action="{$baseurl}/acc.php?action=comment-add" method="post" class="form-horizontal span8">
        <div class="control-group">
            <label for="displayid" class="control-label">Request ID:</label>
            <div class="controls">
                <input type="text" name="displayid" value="{$request->getId()}" disabled="disabled"/>
                <input type="hidden" name="id" value="{$request->getId()}" />
            </div>
        </div>
        <div class="control-group">
            <label for="visibility" class="control-label">Visibility:</label>
            <div class="controls">
                <select name="visibility">
                    <option>user</option>
                    <option>admin</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label for="comment" class="control-label">Comments:</label>
            <div class="controls">
                <textarea name="comment" class="input-xxlarge" rows="6"></textarea>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-default">Submit</button>
            </div>
        </div>
    </form>
</div>

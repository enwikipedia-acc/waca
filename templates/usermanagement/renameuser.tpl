<h2>Rename {$user->getUsername()|escape}</h2>
<form class="form-horizontal" action="users.php?rename={$user->getId()}" method="post">
    <div class="control-group">
        <label class="control-label" for="oldname">Old username:</label>
        <div class="controls">
            <input class="input-xlarge" type="text" id="oldname" value="{$user->getUsername()|escape}" required="true" readonly="true"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="newname">New username:</label>
        <div class="controls">
            <input class="input-xlarge" type="text" id="newname" name="newname" required="true"/>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <button type="submit" class="btn btn-primary">Rename User</button>
        </div>
    </div>
</form>
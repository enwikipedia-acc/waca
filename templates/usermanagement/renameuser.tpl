<h2>Rename {$user->getUsername()|escape}</h2>
<form class="form-horizontal" action="users.php?rename={$user->getId()}" method="post">
    <div class="control-group">
    </div>

    <div class="control-group">
    </div>
    
    <div class="control-group">
	    <div class="controls">
		    <button type="submit" class="btn btn-primary">Rename User</button>
	    </div>
    </div>
</form>
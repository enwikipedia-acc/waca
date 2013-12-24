<h2>Changing access level for {$user->getUsername()|escape} to {$status}</h2>
{include file="alert.tpl" alertblock="0" alerttype="alert-info" alertclosable="0" alertheader="" alertmessage="The user will be shown the reason you enter here. Please keep this in mind."}
<form class="form-horizontal" action="users.php?{$action}={$user->getId()}" method="post">
  <div class="control-group">
    <label class="control-label" for="username">Username:</label>
    <div class="controls">
      <input class="input-xlarge" type="text" id="username" value="{$user->getUsername()|escape}" required="true" readonly="true"/>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="status">Old access level:</label>
    <div class="controls">
      <input class="input-xlarge" type="text" id="status" value="{$user->getStatus()|escape}" required="true" readonly="true"/>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="status">New access level:</label>
    <div class="controls">
      <input class="input-xlarge" type="text" id="status" value="{$status}" required="true" readonly="true"/>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="reason">Reason:</label>
    <div class="controls">
      <textarea id="reason" name="reason" required="true"></textarea>
    </div>
  </div>

  <div class="control-group">
    <div class="controls">
      <button type="submit" class="btn btn-primary">Change access level</button>
    </div>
  </div>
</form>
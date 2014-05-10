<div class="row-fluid">
  <div class="offset4 span4 well">
    <h3 class="text-center">Reset password for {$user->getUsername()|escape} ({$user->getEmail()|escape})</h3>
    <form class="container-fluid" action="{$baseurl}/acc.php?action=forgotpw&amp;si={$si}&amp;id={$id}" method="post">
      <div class="control-group row">
        <input type="password" id="password" name="pw" placeholder="Password" class="offset2 span8" required="required" />
      </div>
      <div class="control-group row">
        <input type="password" id="password" name="pw2" placeholder="Password" class="offset2 span8" required="required" />
      </div>
      <div class="control-group row">
        <button type="submit" class="btn btn-primary btn-block btn-large span8 offset2">Submit</button>
      </div>
      <div class="control-group row">
        <a class="btn btn-block span8 offset2" href="{$baseurl}/acc.php">Return to login</a>
      </div>
    </form>
  </div>
</div>


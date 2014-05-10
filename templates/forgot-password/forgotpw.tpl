<div class="row-fluid">
  <div class="offset4 span4 well">
    <h3 class="text-center">Forgot password?</h3>
    <form class="container-fluid" action="{$baseurl}/acc.php?action=forgotpw" method="post">
      <div class="control-group row">
        <input type="text" id="username" name="username" placeholder="Username" class="offset2 span8" required="required" />
      </div>
      <div class="control-group row">
        <input type="text" id="email" name="email" placeholder="Email" class="offset2 span8" required="required" />
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
<div class="page-header">
  <h1>Register for Tool Access</h1>
</div>
{include file="alert.tpl" alertblock=true alerttype="alert-info" alertclosable=false alertheader="Signing up for Wikipedia? You're not in the right place!" alertmessage="This form is for requesting access to this tool's management interface (used by existing Wikipedians to help you get an account). If you want to request an account for Wikipedia, then <a class=\"btn btn-primary btn-small\" href=\"index.php\">click here</a>"}

<form class="form-horizontal" action="acc.php?action=sreg" method="post">
  <input type="hidden" name="welcomeenable" value="false" />
  <input name="template" value="welcome" type="hidden" />
  <input type="hidden" name="sig" value="" />
  
  <div class="control-group">
    <label for="name" class="control-label">Desired username:</label>
    <div class="controls">
      <input id="name" type="text" name="name" required="required"/>
    </div>
  </div>
  <div class="control-group">
    <label for="pass" class="control-label">Choose a password:</label>
    <div class="controls">
      <input type="password" id="pass" name="pass" required="required" />
      <span class="help-inline">Please do not use the same password you use on Wikipedia!</span>
    </div>
  </div>
  <div class="control-group">
    <label for="pass2" class="control-label">Confirm password:</label>
    <div class="controls">
      <input type="password" id="pass2" name="pass2" required="required" />
    </div>
  </div>

  <div class="control-group">
    <label for="email" class="control-label">E-mail Address:</label>
    <div class="controls">
      <input type="email" id="email" name="email" required="required" />
    </div>
  </div>
  
  {if ! $useOauthSignup}
  <fieldset>
    <legend>You on Wikipedia</legend>
	<div class="control-group">
	  <label for="wname" class="control-label">Wikipedia username:</label>
      <div class="controls">
        <input type="text" id="wname" name="wname" required="required" />
      </div>
    </div>

    <div class="control-group">
      <label for="conf_revid" class="control-label">Confirmation revision ID:</label>
      <div class="controls">
        <input type="text" id="conf_revid" name="conf_revid" required="required" />
        <span class="help-inline">
          <a href="#myModal" role="button" class="btn" data-toggle="modal">Help!</a>
        </span>
        <span class="help-block">This is just to confirm it is you requesting this account. We check that the account you've specified above is the one you've entered here</span>
      </div>
    </div>
  </fieldset>
  {/if}
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        <input type="checkbox" id="guidelines" name="guidelines"  required="required"/>
        I have read and understand the <a href="http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide">interface guidelines</a>.
	  </label>
    </div>
  </div>

  {if $useOauthSignup}
  <div class="control-group">
    <div class="controls">
      {include file="alert.tpl" alertblock=false alerttype="alert-info" alertheader="" alertclosable=false alertmessage="<strong>Heads up!</strong> After you click the Signup button, you will be redirected to Wikipedia, and be prompted to allow this tool access to your Wikipedia account. Please see the guide for more information."}
    </div>
  </div>
  {/if}

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Signup</button>
  </div>
       
</form>

<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-header">
    <h3 id="myModalLabel">Help</h3>
  </div>
  <div class="modal-body">
    <p>Please make an edit to your talk page while logged in.  In this edit, note that you requested an account on the ACC account creation interface.  Failure to do this will result in your request being declined as we will be unable to verify it is you who requested the account.</p>
    <p>Enter the revid of this confirmation edit in this field. (The revid is the number after the &amp;diff= part of the URL of a diff.</p>
  </div>
  <div class="modal-footer">
    <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>
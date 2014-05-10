<div class="page-header">
	<h1>User preferences<small> Change your preferences.</small></h1>
</div>
<form class="form-horizontal" method="post">
<fieldset>
  <legend>General settings</legend>
  <div class="control-group">
    <label class="control-label" for="inputSig">Your signature (wikicode)</label>
    <div class="controls">
      <input class="input-xxlarge" type="text" id="inputSig" name="sig" value="{$currentUser->getWelcomeSig()|escape}" />
        <span class="help-block">This would be the same as ~~~ on-wiki. No date, please.</span>
      </div>
  </div>
	<div class="control-group">
		<label class="control-label" for="inputEmail">Your Email address</label>
		<div class="controls">
			<input class="input-xlarge" type="email" id="inputEmail" name="email" value="{$currentUser->getEmail()|escape}" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="inputEmailsig">Email signature</label>
		<div class="controls">
			<textarea class="field span11" id="inputEmailsig" rows="4" name="emailsig">{$currentUser->getEmailSig()|escape}</textarea><span class="help-block">This will show up at the end of any Email you send through the interface.</span>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<label class="checkbox">
				<input type="checkbox" id="inputAbortpref" name="abortpref"{if $currentUser->getAbortPref()} checked{/if}> Don't ask to double check before closing requests (requires Javascript)
			</label>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn btn-primary">Save preferences</button>
		</div>
	</div>
</fieldset>
</form>

<div class="form-horizontal">
  <fieldset>
    <legend>Wikipedia Account</legend>

    {if $currentUser->isOAuthLinked() }

    <div class="control-group">
      <label class="control-label">Attached Wikipedia account:</label>
      <div class="controls">
        <a href="{$mediawikiScriptPath}?title=User:{$currentUser->getOAuthIdentity()->username|escape:'url'}">{$currentUser->getOAuthIdentity()->username|escape}</a>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label">Email address:</label>
      <div class="controls">
        <div class="alert{if $currentUser->getOAuthIdentity()->confirmed_email} alert-success{/if}">
          {if $currentUser->getOAuthIdentity()->confirmed_email}
            Email address confirmed
          {else}
            Email address <strong>NOT</strong> confirmed
          {/if}
        </div>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label">Blocked:</label>
      <div class="controls">
        <div class="alert{if $currentUser->getOAuthIdentity()->blocked} alert-error{else} alert-success{/if}">
          {if $currentUser->getOAuthIdentity()->blocked}
            <strong>Blocked on Wikipedia!</strong>
          {else}
            Not blocked.
          {/if}
        </div>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label">Grants:</label>
      {foreach from=array('useoauth') item="right"}
      <div class="controls">
          <div class="alert{if in_array($right, $currentUser->getOAuthIdentity()->grants)} alert-success{else} alert-error{/if}">
            {if in_array($right, $currentUser->getOAuthIdentity()->grants)}
              Found: {$right}
            {else}
              <strong>NOT Found: {$right}</strong>
            {/if}
          </div>
        </div>
      {/foreach}
    </div>
    
    <div class="control-group">
      <div class="controls">
        <a href="{$tsurl}/acc.php?action=oauthdetach" class="btn btn-danger">Detach account</a>
      </div>
    </div>
    {else}
    <div class="control-group">
      <label class="control-label">On-wiki username</label>
      <div class="controls">
        <input disabled="disabled" class="input-xlarge" type="text" value="{$currentUser->getOnWikiName()|escape}" />
      </div>
    </div>

    <div class="control-group">
      <div class="controls">
        <a href="{$tsurl}/acc.php?action=oauthattach" class="btn btn-success">Attach account</a>
      </div>
    </div>
    {/if}
  </fieldset>
</div>

<form class="form-horizontal" method="post" action="{$tsurl}/acc.php?action=changepassword">
  <fieldset>
    <legend>Change your password</legend>
    <div class="control-group">
      <label class="control-label" for="inputOldpassword">Your old password</label>
      <div class="controls">
        <input class="input-xlarge" type="password" id="inputOldpassword" name="oldpassword" />
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputNewpassword">Your new password</label>
      <div class="controls">
        <input class="input-xlarge" type="password" id="inputNewpassword" name="newpassword" />
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputNewpasswordconfirm">Confirm new password</label>
      <div class="controls">
        <input class="input-xlarge" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm" />
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <button type="submit" class="btn btn-primary">Update password</button>
      </div>
    </div>
  </fieldset>
</form>
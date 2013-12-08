<div class="page-header">
	<h1>User preferences<small> Change your preferences.</small></h1>
</div>
<fieldset>
  <legend>General settings</legend>
<form class="form-horizontal" method="post">
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
		<label class="control-label">Your on-wiki username</label>
		<div class="controls">
			<span class="input-xlarge uneditable-input">{$currentUser->getOnWikiName()|escape}</span>
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
				<input type="checkbox" id="inputSecureenable" name="secureenable" checked disabled /> Enable use of the secure server
			</label>
      <span class="help-block muted">This setting is deprecated and will be removed at a later date.</span>
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
</form>
</fieldset>
<fieldset>
<legend>Change your password</legend>
<form class="form-horizontal" method="post" action="{$tsurl}/acc.php?action=changepassword">
	<div class="control-group">
		<label class="control-label" for="inputOldpassword">Your old password</label>
		<div class="controls">
			<input class="input-xlarge" type="password" id="inputOldpassword" name="oldpassword">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="inputNewpassword">Your new password</label>
		<div class="controls">
			<input class="input-xlarge" type="password" id="inputNewpassword" name="newpassword">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="inputNewpasswordconfirm">Confirm new password</label>
		<div class="controls">
			<input class="input-xlarge" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm">
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn btn-primary">Update password</button>
		</div>
	</div>
</form>
</fieldset>
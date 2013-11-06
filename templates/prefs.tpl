<div class="page-header">
	<h1>User preferences<small> Change your preferences.</small></h1>
</div>
<h2>General settings</h2>
<form class="form-horizontal" method="post">
	<div class="control-group">
		<label class="control-label" for="inputSig">Your signature (wikicode)</label>
		<div class="controls">
			<input class="input-xlarge" type="text" id="inputSig" name="sig" value="{$sig}"><span class="help-block">This would be the same as ~~~ on-wiki. No date, please.</span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="inputEmail">Your Email address</label>
		<div class="controls">
			<input class="input-xlarge" type="email" id="inputEmail" name="email" value="{$email}">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">Your on-wiki username</label>
		<div class="controls">
			<span class="input-xlarge uneditable-input">{$onwikiname}</span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="inputEmailsig">Email signature</label>
		<div class="controls">
			<textarea class="field span11" id="inputEmailsig" rows="4" name="emailsig">{$emailsig}</textarea><span class="help-block">This will show up at the end of any Email you send through the interface.</span>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<label class="checkbox">
				<input type="checkbox" id="inputSecureenable" name="secureenable"{if $securepref == 1} checked{/if}> Enable use of the secure server
			</label>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<label class="checkbox">
				<input type="checkbox" id="inputAbortpref" name="abortpref"{if $abortpref == 1} checked{/if}> Don't ask to double check before closing requests (requires Javascript)
			</label>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn">Submit query</button>
		</div>
	</div>
</form>
<h2>Change your password</h2>
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
			<button type="submit" class="btn">Submit query</button>
		</div>
	</div>
</form>

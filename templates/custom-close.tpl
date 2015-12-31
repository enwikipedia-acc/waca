<form action="?{$querystring}" method="post" class="form-horizontal">
  <fieldset>
    <legend>Custom close{if $preloadtitle != ""} - {$preloadtitle|escape}{/if}</legend>

    <div class="control-group">
      <label for="msgbody" class="control-label">Request information</label>
      <div class="controls">
        {include file="zoom-parts/request-info.tpl" hash="x" showinfo=true showLink=false}
      </div>
    </div>
    
    <div class="control-group">
      <label for="msgbody" class="control-label">Message to be sent to the user:</label>
      <div class="controls">
        {include file="alert.tpl" alertblock="1" alerttype="alert-error" alertclosable="0" alertheader="Caution!"
        alertmessage="The contents of this box will be sent as an email to the user with the signature set in <a href=\"$baseurl/acc.php?action=prefs\">your preferences</a> appended to it. <strong>If you do not set a signature in your preferences, please manually enter one at the end of your message</strong>."}
        <textarea id="msgbody" name="msgbody" rows="15" class="input-block-level">{$preloadtext|escape}</textarea>
      </div>
    </div>

	<div class="control-group">
		<label class="control-label" for="inputAction">Action to take</label>
		<div class="controls">
			<select class="input-xlarge" id="inputAction" name="action" required="required">
				<option value="" {if $defaultAction == ""}selected="selected"{/if}>(please select)</option>
				<option value="mail">Only send the email</option>
				<optgroup label="Send email and close request...">
					<option value="created" {if $defaultAction == "created"}selected="selected"{/if}>Close request as created</option>
					<option value="not created" {if $defaultAction == "not created"}selected="selected"{/if}>Close request as NOT created</option>
				</optgroup>
				<optgroup label="Send email and defer to...">
					{foreach $requeststates as $state}
					<option value="{$state@key}" {if $defaultAction == $state@key}selected="selected"{/if}>Defer to {$state.deferto|capitalize}</option>
					{/foreach}
				</optgroup>
			</select>
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
			<label class="checkbox">
				<input type="checkbox" name="ccmailist" checked="checked"
					   {if !$currentUser->isAdmin() && !$currentUser->isCheckuser()}disabled="disabled"{/if}
				/>
				CC to mailing list
			</label>
		</div>
	</div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Close and send</button>
      <a href="?action=zoom&amp;id={$request->getId()}" class="btn">Cancel</a>
    </div>
  </fieldset>
</form>


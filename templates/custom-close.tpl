<form action="?{$querystring}" method="post" class="form-horizontal">
  <fieldset>
    <legend>Custom close{if $preloadtitle != ""} - {$preloadtitle|escape}{/if}</legend>


    <div class="control-group">
      <label for="msgbody" class="control-label">Message to be sent to the user:</label>
      <div class="controls">
        {include file="alert.tpl" alertblock="1" alerttype="alert-error" alertclosable="0" alertheader="Caution!"
        alertmessage="The contents of this box will be sent as an email to the user with the signature set in <a href=\"$tsurl/acc.php?action=prefs\">your preferences</a> appended to it. <strong>If you do not set a signature in your preferences, please manually enter one at the end of your message</strong>."}
        <textarea id="msgbody" name="msgbody" rows="15" class="input-block-level">{$preloadtext|escape}</textarea>
      </div>
    </div>

    <div class="control-group">
      <div class="controls">
        <label class="checkbox">
          <input type="checkbox" name="created" {if $forcreated}checked="checked"{/if}/>Account created
        </label>
        
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


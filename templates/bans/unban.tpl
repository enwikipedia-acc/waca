<form class="form-horizontal" method="post" action="{$baseurl}/acc.php?action=unban&amp;confirmunban=true&amp;id={$ban->getId()}">
  <fieldset>
    <legend>Unbanning {$ban->getTarget()|escape}</legend>

    <p>Are you sure you wish to unban <code>{$ban->getTarget()|escape}</code>, which is {if $ban->getDuration() == "-1"} not set to expire {else} set to expire {date("Y-m-d H:i:s", $ban->getDuration())}{/if} with the following reason?</p>
    <pre>{$ban->getReason()|escape}</pre>

    <div class="control-group">
      <label class="control-label">Reason for unbanning {$ban->getTarget()|escape}</label>
      <div class="controls">
        <input type="text" class="input-xxlarge" name="unbanreason" required="true"/>
      </div>
    </div>
  </fieldset>
  <div class="form-actions">
    <a class="btn" href="{$baseurl}/acc.php?action=ban">Cancel</a>
    <button type="submit" class="btn btn-success">
      <i class="icon-white icon-ok"></i>&nbsp;Unban
    </button>
  </div>
</form>
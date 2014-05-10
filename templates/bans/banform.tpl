<h2>Ban an IP, name, or email address</h2>
<form class="form-horizontal" action="{$baseurl}/acc.php?action=sban" method="post">
  <div class="control-group">
    <label class="control-label">Type:</label>
    <div class="controls">
      <select name="type" required="true">
		<option value="IP"{if $bantype == "IP"} selected="true"{else if $bantype != ""} disabled ="true"{/if}>IP</option>
		<option value="Name"{if $bantype == "Name"} selected="true"{else if $bantype != ""} disabled ="true"{/if}>Name</option>
		<option value="EMail"{if $bantype == "EMail"} selected="true"{else if $bantype != ""} disabled ="true"{/if}>E-Mail</option>
      </select>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label">Ban Target:</label>
    <div class="controls">
      <input type="text" name="target" {if $bantarget != ""} readonly="true" value="{$bantarget|escape}"{/if} required="true"/>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label">Reason:</label>
    <div class="controls">
      <input type="text" class="input-xxlarge" name="banreason" required="true"/>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label">Duration:</label>
    <div class="controls">
      <select name="duration" required="true">
        <option value="-1">Indefinite</option>
        <option value="86400">24 Hours</option>
        <option value="604800">One Week</option>
        <option value="2629743">One Month</option>
        <option value="other">Other</option>
      </select>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label">Other duration:</label>
    <div class="controls">
      <input type="text" name="otherduration" />
    </div>
  </div>
  
  <div class="form-actions">
    <button type="submit" class="btn btn-danger">
      <i class="icon-white icon-ban-circle"></i>&nbsp;Ban</button>
  </div>
</form>

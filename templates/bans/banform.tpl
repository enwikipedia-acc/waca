{extends file="pagebase.tpl"}
{block name="content"}
    <h3>Ban an IP, name, or email address</h3>
    <form class="form-horizontal" method="post">
        <div class="control-group">
            <label class="control-label" for="banType">Type:</label>
            <div class="controls">
                <select name="type" required="required" id="banType">
                    <option value="IP"{if $bantype == "IP"} selected="selected"{elseif $bantype != ""} disabled="disabled"{/if}>
                        IP
                    </option>
                    <option value="Name"{if $bantype == "Name"} selected="selected"{elseif $bantype != ""} disabled="disabled"{/if}>
                        Name
                    </option>
                    <option value="EMail"{if $bantype == "EMail"} selected="selected"{elseif $bantype != ""} disabled="disabled"{/if}>
                        E-Mail
                    </option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="banTarget">Ban Target:</label>
            <div class="controls">
                <input type="text"
                       id="banTarget"
                       name="target" {if $bantarget != ""} readonly="readonly" value="{$bantarget|escape}"{/if}
                       required="required"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="banReason">Reason:</label>
            <div class="controls">
                <input type="text" id="banReason" class="input-xxlarge" name="banreason" required="required"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="banDuration">Duration:</label>
            <div class="controls">
                <select name="duration" required="required" id="banDuration">
                    <option value="-1">Indefinite</option>
                    <option value="86400">24 Hours</option>
                    <option value="604800">One Week</option>
                    <option value="2629743">One Month</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="otherDuration">Other duration:</label>
            <div class="controls">
                <input type="text" id="otherDuration" name="otherduration"/>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-danger">
                <i class="icon-white icon-ban-circle"></i>&nbsp;Ban
            </button>
        </div>
    </form>
{/block}
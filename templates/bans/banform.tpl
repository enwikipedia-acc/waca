{extends file="pagebase.tpl"}
{block name="content"}
    <h3>Ban an IP, name, or email address</h3>
    <form method="post">
        {include file="security/csrf.tpl"}

        <div class="form-group">
            <label for="banType">Type:</label>
            <select class="form-control" name="type" required="required" id="banType">
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

        <div class="form-group">
            <label for="banTarget">Ban Target:</label>
            <input type="text"
                   class="form-control" id="banTarget"name="target" {if $bantarget != ""} readonly="readonly" value="{$bantarget|escape}"{/if} required="required"/>
        </div>

        <div class="form-group">
            <label for="banReason">Reason:</label>
            <input type="text" class="form-control" id="banReason" class="input-xxlarge" name="banreason" required="required"/>
        </div>

        <div class="form-group">
            <label for="banDuration">Duration:</label>
            <select class="form-control" name="duration" required="required" id="banDuration">
                <option value="-1">Indefinite</option>
                <option value="86400">24 Hours</option>
                <option value="604800">One Week</option>
                <option value="2629743">One Month</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="otherDuration">Other duration:</label>
            <input class="form-control" type="text" id="otherDuration" name="otherduration"/>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-ban"></i>&nbsp;Ban
            </button>
        </div>
    </form>
{/block}

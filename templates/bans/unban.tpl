{extends file="pagebase.tpl"}
{block name="content"}
    <form method="post">
        {include file="security/csrf.tpl"}
        <fieldset>
            <legend>Unbanning {$ban->getTarget()|escape}</legend>

            <p>Are you sure you wish to unban <code>{$ban->getTarget()|escape}</code>, which
                is {if $ban->getDuration() == "-1"} not set to expire {else} set to expire {date("Y-m-d H:i:s", $ban->getDuration())}{/if}
                with the following reason?</p>
            <pre>{$ban->getReason()|escape}</pre>

            <div class="form-group">
                <label for="unbanreason">Reason for unbanning {$ban->getTarget()|escape}</label>
                <input class="form-control" type="text" id="unbanreason" name="unbanreason" required="required"/>
            </div>
        </fieldset>

        <input type="hidden" name="updateversion" value="{$ban->getUpdateVersion()}" />

        <div class="form-actions">
            <a class="btn" href="{$baseurl}/internal.php/bans">Cancel</a>
            <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i>&nbsp;Unban</button>
        </div>
    </form>
{/block}

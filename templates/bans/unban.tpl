{extends file="pagebase.tpl"}
{block name="content"}
    <form class="form-horizontal" method="post">
        <fieldset>
            <legend>Unbanning {$ban->getTarget()|escape}</legend>

            <p>Are you sure you wish to unban <code>{$ban->getTarget()|escape}</code>, which
                is {if $ban->getDuration() == "-1"} not set to expire {else} set to expire {date("Y-m-d H:i:s", $ban->getDuration())}{/if}
                with the following reason?</p>
            <pre>{$ban->getReason()|escape}</pre>

            <div class="control-group">
                <label class="control-label" for="unbanreason">Reason for unbanning {$ban->getTarget()|escape}</label>
                <div class="controls">
                    <input type="text" class="input-xxlarge" id="unbanreason" name="unbanreason" required="required"/>
                </div>
            </div>
        </fieldset>

        <div class="form-actions">
            <a class="btn" href="{$baseurl}/internal.php/bans">Cancel</a>
            <button type="submit" class="btn btn-success"><i class="icon-white icon-ok"></i>&nbsp;Unban</button>
        </div>
    </form>
{/block}
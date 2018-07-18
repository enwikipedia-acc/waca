{extends file="pagebase.tpl"}
{block name="content"}
    <h2>Edit template</h2>

    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="form-group">
            <label for="usercode">Display code:</label>
            <input type="text" class="form-control" name="usercode" id="usercode"
                   value="{$template->getUserCode()|escape}" required="required"/>
            <span class="help-block">This will be displayed to the user when choosing a template</span>
        </div>
        <div class="form-group">
            <label for="botcode">Bot code:</label>
            <textarea class="form-control" rows="10" name="botcode" id="botcode"
                      required="required">{$template->getBotCode()|escape}</textarea>
            <span class="help-block">This is what will be placed on the page by the bot. <code>$username</code> will be replaced with the account creator's username, and <code>$signature</code> with their signature and a timestamp. Please don't use <code>~~~~</code>
             here as that will use the bot's signature.</span>
        </div>

        <input type="hidden" name="updateversion" value="{$template->getUpdateVersion()}"/>

        <div class="form-group">
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-check"></i>&nbsp;Save changes
            </button>
        </div>
    </form>
{/block}

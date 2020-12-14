{extends file="pagebase.tpl"}
{block name="content"}
    <h2>{if $createmode}Create new{else}Edit{/if} Welcome Template</h2>

    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="form-group">
            <label for="usercode">Display name:</label>
            <input type="text" class="form-control" name="usercode" id="usercode" required="required"
                   value="{$template->getUserCode()|escape}" />
            <span class="help-block">This will be displayed to the user when choosing a template</span>
        </div>
        <div class="form-group">
            <label for="botcode">Wikitext:</label>
            <textarea class="form-control" rows="10" name="botcode" id="botcode" required="required">{$template->getBotCode()|escape}</textarea>
            <div class="help-block">This is what will be placed on the new user's talk page as a new section.
                <code>$request</code> will be replaced with the requested username. <code>$creator</code> will be replaced with the creator's on-wiki username.</div>
        </div>

        <input type="hidden" name="updateversion" value="{$template->getUpdateVersion()}"/>

        <div class="form-group">
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-check"></i>&nbsp;Save changes
            </button>
        </div>
    </form>
{/block}

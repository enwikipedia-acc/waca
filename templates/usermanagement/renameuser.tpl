{extends file="pagebase.tpl"}
{block name="content"}
    <h3>Rename {$user->getUsername()|escape}</h3>
    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="form-group">
            <label for="oldname">Old username:</label>
            <input class="form-control" type="text" id="oldname" value="{$user->getUsername()|escape}" required="required" readonly="readonly"/>
        </div>

        <div class="form-group">
            <label for="newname">New username:</label>
            <input class="form-control" type="text" id="newname" name="newname" required="required"/>
        </div>

        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}" />

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Rename User</button>
        </div>
    </form>
{/block}

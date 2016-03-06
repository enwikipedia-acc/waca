{extends file="pagebase.tpl"}
{block name="content"}
    <h3>Rename {$user->getUsername()|escape}</h3>
    <form class="form-horizontal" method="post">
        <div class="control-group">
            <label class="control-label" for="oldname">Old username:</label>
            <div class="controls">
                <input class="input-xlarge" type="text" id="oldname" value="{$user->getUsername()|escape}"
                       required="required" readonly="readonly"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="newname">New username:</label>
            <div class="controls">
                <input class="input-xlarge" type="text" id="newname" name="newname" required="required"/>
            </div>
        </div>

        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}" />

        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary">Rename User</button>
            </div>
        </div>
    </form>
{/block}
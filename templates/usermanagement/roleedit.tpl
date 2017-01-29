{extends file="pagebase.tpl"}
{block name="content"}
    <h3>Editing assigned roles for {$user->getUsername()|escape}</h3>

    <form class="form-horizontal" method="post">
        {include file="security/csrf.tpl"}

        <div class="control-group">
            <div class="controls">
                {foreach from=$roleData key='role' item='data'}
                    <label class="checkbox">
                        <input type="checkbox" name="role-{$role|escape}" {if $data['allowEdit'] === 0}disabled="disabled"{/if} {if $data['active'] === 1}checked="checked"{/if} />
                        <code>{$role|escape}</code> {$data['description']|escape}
                    </label>
                {/foreach}
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="reason">Reason:</label>
            <div class="controls">
                <textarea id="reason" name="reason" required="required" class="input-xxlarge" rows="5"></textarea>
            </div>
        </div>

        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}"/>

        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>
{/block}

{extends file="pagebase.tpl"}
{block name="content"}
    <h3>Editing assigned roles for {$user->getUsername()|escape}</h3>

    <form method="post">
        {include file="security/csrf.tpl"}

        {foreach from=$roleData key='role' item='data'}
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="role-{$role|escape}" id="role-{$role|escape}" {if $data['allowEdit'] === 0}disabled="disabled"{/if} {if $data['active'] === 1}checked="checked"{/if} />
                <label class="form-check-label" for="role-{$role|escape}">
                    <code>{$role|escape}</code> {$data['description']|escape}
                </label>
            </div>
        {/foreach}

        <div class="form-group">
            <label for="reason">Reason:</label>
            <textarea id="reason" name="reason" required="required" class="form-control" rows="5"></textarea>
        </div>

        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}"/>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
{/block}

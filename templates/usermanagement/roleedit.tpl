{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">User Management <small class="text-muted">Approve, deactivate, promote, demote, etc.</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h3>Editing assigned roles for {$user->getUsername()|escape}</h3>
        </div>
    </div>

    <form method="post">
        {include file="security/csrf.tpl"}

        <div class="row mb-3">
            <div class="d-none d-md-block col-md-3 col-lg-2">
                <span class="col-form-label">Editable roles:</span>
            </div>
            <div class="col-md-9 col-lg-10 border-bottom">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
                    {foreach from=$roleData key='role' item='data'}
                        {if $data['allowEdit'] === 1}
                        <div class="col">
                            <div class="custom-control custom-switch mb-2">
                                <input class="custom-control-input" type="checkbox" name="role-{$role|escape}" id="role-{$role|escape}" {if $data['allowEdit'] === 0}disabled="disabled"{/if} {if $data['active'] === 1}checked="checked"{/if} />
                                <label class="custom-control-label" for="role-{$role|escape}">
                                    <code>{$role|escape}</code>
                                    {if $data['globalOnly']}<span class="badge badge-dark">Global role</span>{/if}
                                    <span class="form-text text-muted">{$data['description']|escape}</span>
                                </label>
                            </div>
                        </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 col-lg-2 d-none d-md-block">
                <span class="col-form-label">Locked roles:</span>
            </div>
            <div class="col-md-9 col-lg-10 d-none d-md-block ">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
                    {foreach from=$roleData key='role' item='data'}
                        {if $data['allowEdit'] === 0}
                        <div class="col">
                            <div class="custom-control custom-switch mb-2">
                                <input class="custom-control-input" type="checkbox" name="role-{$role|escape}" id="role-{$role|escape}" disabled="disabled" {if $data['active'] === 1}checked="checked"{/if} />
                                <label class="custom-control-label" for="role-{$role|escape}">
                                    <code>{$role|escape}</code>
                                    {if $data['globalOnly']}<span class="badge badge-dark">Global role</span>{/if}
                                    <span class="form-text text-muted">{$data['description']|escape}</span>
                                </label>
                            </div>
                        </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-lg-2">
                <label class="col-form-label" for="reason">Reason:</label>
            </div>
            <div class="col-md-9 col-lg-10">
                <textarea id="reason" name="reason" required="required" class="form-control" rows="5"></textarea>
            </div>
        </div>


        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}"/>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
{/block}

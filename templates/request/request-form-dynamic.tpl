{extends file="publicbase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            {$formPreamble}
        </div>
    </div>
    <form method="post">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="container-fluid g-0">
                <div class="row mb-3">
                    <label for="inputUsername" class="form-label col-md-4">Username</label>
                    <div class="col-md-8">
                        <input class="form-control" type="text" id="inputUsername" placeholder="Username" name="name"
                               required="required" value="{$username|default:''|escape}" {if $formIsDisabled}disabled{/if}>
                        <small class="form-text text-muted">
                            {$formUsernameHelp}
                        </small>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="inputEmail" class="col-md-4 form-label">Email</label>
                    <div class="col-md-8">
                        <input class="form-control" type="email" id="inputEmail" placeholder="Email" name="email"
                               required="required" value="{$email|default:''|escape}" {if $formIsDisabled}disabled{/if}>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="inputEmailConfirm" class="col-md-4 form-label">Confirm Email</label>
                    <div class="col-md-8">
                        <input class="form-control" type="email" id="inputEmailConfirm" placeholder="Confirm Email"
                               name="emailconfirm" required="required" {if $formIsDisabled}disabled{/if}>
                        <small class="form-text text-muted">
                            {$formEmailHelp}
                        </small>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="inputComments" class="col-md-4 form-label">Comments</label>
                    <div class="col-md-8">
                        <textarea class="form-control" id="inputComments" rows="4" name="comments" {if $formIsDisabled}disabled{/if}>{$comments|default:''|escape}</textarea>
                        <small class="form-text text-muted">
                            {$formCommentsHelp}
                        </small>
                    </div>
                </div>
                <div class="row mb-3">
                    {include file="request/legal-info.tpl"}
                </div>
                <div class="row mb-3">
                    <{if $formIsDisabled}div{else}button type="submit"{/if} class="offset-md-4 col-md-8 btn btn-primary btn-block {if $formIsDisabled}disabled{/if}">
                        Send request
                    </{if $formIsDisabled}div{else}button{/if}>
                </div>
            </div>
        </div>
    </div>
    </form>
{/block}

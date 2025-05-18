{extends file="publicbase.tpl"}
{assign var="skinBaseline" value="5"}
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
                        <input class="form-control" type="text" id="inputUsername" placeholder="Username" name="name" required="required" value="{$username|default:''|escape}">
                        <small class="form-text text-muted">
                            {$formUsernameHelp}
                        </small>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="inputEmail" class="col-md-4 form-label">Email</label>
                    <div class="col-md-8">
                        <input class="form-control" type="email" id="inputEmail" placeholder="Email" name="email" required="required" value="{$email|default:''|escape}"></div>
                </div>
                <div class="row mb-3">
                    <label for="inputEmailConfirm" class="col-md-4 form-label">Confirm Email</label>
                    <div class="col-md-8">
                        <input class="form-control" type="email" id="inputEmailConfirm" placeholder="Confirm Email" name="emailconfirm" required="required">
                        <small class="form-text text-muted">
                            {$formEmailHelp}
                        </small>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="inputComments" class="col-md-4 form-label">Comments</label>
                    <div class="col-md-8">
                        <textarea class="form-control" id="inputComments" rows="4" name="comments">{$comments|default:''|escape}</textarea>
                        <small class="form-text text-muted">
                            {$formCommentsHelp}
                        </small>
                    </div>
                </div>
                <div class="row mb-3">
                    {include file="request/legal-info.tpl"}
                </div>
                <div class="row mb-3">
                    <button type="submit" class="offset-md-4 col-md-8 btn btn-primary btn-block">Send request</button>
                </div>
            </div>
        </div>
    </div>
    </form>
{/block}

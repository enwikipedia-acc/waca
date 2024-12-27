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
        <div class="col-12"><h3>User Settings for {$user->getUsername()|escape}</h3></div>
    </div>

    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="form-group row">
            <div class="offset-lg-2 col-md-3 col-lg-2">
                <label for="user_name" class="col-form-label">Username:</label>
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4">
                <input class="form-control" type="text" id="user_name" value="{$user->getUsername()|escape}"
                       required="required" readonly="readonly"/>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-lg-2 col-md-3 col-lg-2"><label for="user_status" class="col-form-label">User status:</label></div>
            <div class="col-md-4 col-lg-3 col-xl-2">
                <input class="form-control" type="text" id="user_status" value="{$user->getStatus()|escape}" required="required" readonly="readonly"/>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-lg-2 col-md-3 col-lg-2"><label for="user_email" class="col-form-label">Email Address:</label></div>
            <div class="col-md-8 col-lg-6 col-xl-4">
                <input class="form-control" type="email" id="user_email" name="user_email"
                    value="{$user->getEmail()|escape}" required="required"/>
            </div>
        </div>

        {if $oauth->isFullyLinked() || $oauth->isPartiallyLinked()}
            <div class="form-group row">
                <div class="offset-lg-2 col-md-3 col-lg-2"><label for="user_onwikiname" class="col-form-label">On-wiki Username:</label></div>
                <div class="col-md-8 col-lg-6 col-xl-4">
                    <input class="form-control" type="text" id="user_onwikiname" value="{$user->getOnWikiName()|escape}" readonly="readonly"/>
                    <span class="badge {if $oauth->isPartiallyLinked()}badge-danger{else}badge-success{/if}">OAuth</span>
                </div>
            </div>
        {else}
            <div class="form-group row">
                <div class="offset-lg-2 col-md-3 col-lg-2"><label for="user_onwikiname" class="col-form-label">On-wiki Username:</label></div>
                <div class="col-md-8 col-lg-6 col-xl-4">
                    <input class="form-control" type="text" id="user_onwikiname" name="user_onwikiname"
                           value="{$user->getOnWikiName()|escape}" required="required"/>
                </div>
            </div>
        {/if}

        <div class="form-group row">
            <div class="offset-lg-2 col-md-3 col-lg-2">
                <label for="inputEmailsig" class="col-form-label">Email signature</label>
            </div>
            <div class="col-md-9 col-lg-7 col-xl-6">
                <textarea class="form-control" rows="5" name="user_emailsig" id="inputEmailsig" readonly>{$emailSignature|escape}</textarea>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-lg-2 col-md-3 col-lg-2">
                <label class="col-form-label">Account Creation Mode</label>
            </div>
            <div class="col-md-9 col-lg-7 col-xl-6">
                <div class="alert alert-info alert-block">
                    Beware of setting this value to one the user can't use. It won't break things, but it will make things inconvenient.
                </div>
                <div class="custom-control custom-radio">
                    <input type="radio" name="creationmode" value="0" class="custom-control-input" id="autocreateNone"
                           {if $preferredCreationMode == 0}checked="checked"{/if} />
                    <label class="custom-control-label" for="autocreateNone">
                        {if !$canManualCreate}<span class="badge badge-danger">Not authorised</span>{/if}
                        Create accounts manually using Special:CreateAccount
                    </label>
                </div>

                <div class="custom-control custom-radio">
                    <input type="radio" name="creationmode" value="1" class="custom-control-input" id="autocreateOauth"
                           {if $preferredCreationMode == 1}checked="checked"{/if} />
                    <label class="custom-control-label" for="autocreateOauth">
                        {if !$canOauthCreate}<span class="badge badge-danger">Not authorised</span>{/if}
                        Use my Wikimedia account to create the accounts on my behalf where possible
                    </label>
                </div>

                <div class="custom-control custom-radio">
                    <input type="radio" name="creationmode" value="2" class="custom-control-input" id="autocreateBot"
                           {if $preferredCreationMode == 2}checked="checked"{/if} />
                    <label class="custom-control-label" for="autocreateBot">
                        {if !$canBotCreate}<span class="badge badge-danger">Not authorised</span>{/if}
                        Use a bot to create the accounts on my behalf where possible
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">

            <div class="offset-lg-2 col-md-3 col-lg-2">
                <span class="col-form-label">Account reactivation</span>
            </div>
            <div class="col-md-9 col-lg-7 col-xl-6">
                <div class="custom-control custom-switch">
                    <input class="custom-control-input" type="checkbox" id="preventReactivation" name="preventReactivation"{if $preventReactivation} checked{/if}>
                    <label class="custom-control-label" for="preventReactivation">Prevent this user from appealing deactivation</label>
                </div>
            </div>
        </div>



        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}"/>

        <div class="form-group row">
            <div class="offset-md-3 offset-lg-4 col-md-3"><button type="submit" class="btn btn-primary btn-block">Save preferences</button></div>
        </div>
    </form>
{/block}

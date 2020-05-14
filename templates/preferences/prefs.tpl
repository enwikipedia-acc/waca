{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">User preferences <small class="text-muted">Change your preferences</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form method="post">
                {include file="security/csrf.tpl"}
                <fieldset>
                    <legend>General settings</legend>
                    {if !$currentUser->isOAuthLinked() }
                    <div class="form-group row">
                        <div class="col-md-2 col-lg-3">
                            <label for="inputSig" class="col-form-label">Your signature (wikicode)</label>
                        </div>
                        <div class="col-md-10 col-lg-8 col-xl-6">
                            <input class="form-control" type="text" id="inputSig" name="sig" value="{$currentUser->getWelcomeSig()|escape}"/>
                            <small class="form-text text-muted">This would be the same as ~~~ on-wiki. No date, please.</small>
                        </div>
                    </div>
                    {else}
                        <input type="hidden" name="sig" value=""/>
                    {/if}

                    <div class="form-group row">
                        <div class="col-md-2 col-lg-3">
                            <label for="inputEmail" class="col-form-label">Your Email address</label>
                        </div>
                        <div class="col-md-10 col-lg-8 col-xl-6">
                            <input class="form-control" type="email" id="inputEmail" name="email" required="required" value="{$currentUser->getEmail()|escape}"/>
                            <small class="form-text text-muted">This is used to send you automatic notifications about events involving your account in the tool, including sending password reset emails.</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-2 col-lg-3">
                            <label for="inputEmailsig" class="col-form-label">Email signature</label>
                        </div>
                        <div class="col-md-10 col-lg-8 col-xl-6">
                            <textarea class="form-control" id="inputEmailsig" rows="4" name="emailsig">{$currentUser->getEmailSig()|escape}</textarea>
                            <small class="form-text text-muted">This will show up at the end of any Email you send through the interface.</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="offset-md-2 offset-lg-3 col-md-10 col-lg-8 col-xl-6">
                            <div class="custom-control custom-switch">
                                <input class="custom-control-input" type="checkbox" id="inputAbortpref" name="abortpref"{if $currentUser->getAbortPref()} checked{/if}>
                                <label class="custom-control-label" for="inputAbortpref">Skip double-check prompt before closing requests</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="offset-md-2 offset-lg-3 col-md-4 col-lg-3">
                            <button type="submit" class="btn btn-primary btn-block">Save preferences</button>
                        </div>
                    </div>

                </fieldset>
            </form>

        </div>
    </div>

    <fieldset>
        <legend>Wikipedia Account</legend>

        {if $currentUser->isOAuthLinked() && $currentUser->getOnWikiName() != "##OAUTH##" }
            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="get-oauth" class="col-form-label">Attached Wikipedia account:</label>
                </div>
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <a class="form-control" id="get-oauth" href="{$mediawikiScriptPath}?title=User:{$currentUser->getOAuthIdentity()->username|escape:'url'}">{$currentUser->getOAuthIdentity()->username|escape}</a>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="oauth-identity">Identity:</label>
                </div>
                <div class="col-md-10 col-lg-9">
                    <div class="row row-cols-1 row-cols-xl-3 row-cols-lg-2" id="oauth-identity">
                        <div class="col">
                            <div class="alert-block alert{if $currentUser->getOAuthIdentity()->confirmed_email} alert-success{/if}">
                                {if $currentUser->getOAuthIdentity()->confirmed_email}
                                    <i class="fas fa-check"></i>
                                    &nbsp;Email address confirmed
                                {else}
                                    <i class="fas fa-times"></i>
                                    &nbsp;Email address
                                    <strong>NOT</strong>
                                    confirmed
                                {/if}
                            </div>
                        </div>
                        <div class="col">
                            <div class="alert-block alert{if $currentUser->getOAuthIdentity()->blocked} alert-danger{else} alert-success{/if}">
                                {if $currentUser->getOAuthIdentity()->blocked}
                                    <i class="fas fa-times"></i>
                                    &nbsp;
                                    <strong>Blocked on Wikipedia!</strong>
                                {else}
                                    <i class="fas fa-check"></i>
                                    &nbsp;Not blocked.
                                {/if}
                            </div>
                        </div>
                        <div class="col">
                            <div class="alert-block alert alert-success">
                                <i class="fas fa-check"></i>&nbsp;Account verified by {$currentUser->getOAuthIdentity()->iss}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="offset-md-2 offset-lg-3 col-md-10 col-lg-9">
                    <div class="accordion" id="identityTicketContainer">
                        <div class="card">
                            <div class="card-header position-relative py-0">
                                <a class="accordion-toggle stretched-link" data-toggle="collapse"
                                   data-parent="#identityTicketContainer" href="#identityTicketCollapseOne">
                                    Show identity ticket
                                </a>
                            </div>
                            <div id="identityTicketCollapseOne" class="collapse out">
                                <div class="card-body">
                                    <pre>{json_encode($currentUser->getOAuthIdentity(), 128)}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="grants-card">Grants:</label>
                </div>
                <div class="col-md-10 col-lg-9">
                    <div class="row row-cols-1 row-cols-xl-3 row-cols-lg-2" id="grants-card">
                        <div class="col">
                            <div class="alert{if $currentUser->oauthCanUse()} alert-success{else} alert-danger{/if} alert-block">
                                <i class="fas fa-{if $currentUser->oauthCanUse()}check{else}times{/if}"></i>&nbsp;Basic
                                rights
                            </div>
                        </div>
                        {*
                          <div class="col">
                              <div class="alert{if $currentUser->oauthCanEdit()} alert-success{else} alert-danger{/if} alert-block">
                                <i class="fas fa-{if $currentUser->oauthCanEdit()}check{else}times{/if}"></i>&nbsp;Create, edit, and move pages
                              </div>
                          </div>
                          <div class="col">
                              <div class="alert{if $currentUser->oauthCanCreateAccount()} alert-success{else} alert-danger{/if} alert-block">
                                <i class="fas fa-{if $currentUser->oauthCanCreateAccount()}check{else}times{/if}"></i>&nbsp;Create accounts
                              </div>
                          </div>
                        *}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label class="control-label">Cache:</label>
                </div>
                <div class="col-md-10 col-lg-9">
                    Identity ticket retrieved
                    at {DateTime::createFromFormat("U", $currentUser->getOAuthIdentity()->iat)->format("r")}, will
                    expire at {DateTime::createFromFormat("U", $currentUser->getOAuthIdentity()->exp)->format("r")}
                </div>
            </div>

            {if !$enforceOAuth }
                <div class="form-group row">
                    <div class="offset-md-2 offset-lg-3 col-md-4 col-lg-3">
                        <a href="{$baseurl}/internal.php/oauth/detach" class="btn btn-block btn-danger">Detach account</a>
                    </div>
                </div>
            {/if}
        {else}
            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="onwikiusername" class="col-form-label">On-wiki username</label>
                </div>
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <input disabled="disabled" class="form-control" type="text" id="onwikiusername" value="{$currentUser->getOnWikiName()|escape}"/>
                </div>
            </div>

            <div class="form-group row">
                <div class="offset-md-2 offset-lg-3 col-md-4 col-lg-3">
                    <form method="post" action="{$baseurl}/internal.php/oauth/attach">
                        {include file="security/csrf.tpl"}
                        <button type="submit" class="btn btn-block btn-success">Attach account</button>
                    </form>
                </div>
            </div>

        {/if}
    </fieldset>

    <form method="post" action="{$baseurl}/internal.php/preferences/changePassword">
        {include file="security/csrf.tpl"}
        <fieldset>
            <legend>Change your password</legend>

            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="inputOldpassword" class="col-form-label">Your old password</label>
                </div>
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <input class="form-control" type="password" id="inputOldpassword" name="oldpassword" required="required"/>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="inputNewpassword" class="col-form-label">Your new password</label>
                </div>
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <input class="form-control" type="password" id="inputNewpassword" name="newpassword" required="required"/>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="inputNewpasswordconfirm" class="col-form-label">Confirm new password</label>
                </div>
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <input class="form-control" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm" required="required"/>
                </div>
            </div>

            <div class="form-group row">
                <div class="offset-md-2 offset-lg-3 col-md-4 col-lg-3">
                    <button type="submit" class="btn btn-primary btn-block">Update password</button>
                </div>
            </div>

        </fieldset>
    </form>
{/block}

{extends file="pagebase.tpl"}
{block name="content"}
    <div class="jumbotron">
        <h1>User preferences</h1>
        <p> Change your preferences.</p>
    </div>
    <form method="post">
        {include file="security/csrf.tpl"}
        <fieldset>
            <legend>General settings</legend>
            <div class="form-group">
                {if !$currentUser->isOAuthLinked() }
                    <label for="inputSig">Your signature (wikicode)</label>
                    <input class="form-control" type="text" id="inputSig" name="sig"
                           value="{$currentUser->getWelcomeSig()|escape}"/>
                    <span class="help-block">This would be the same as ~~~ on-wiki. No date, please.</span>
                {else}
                    <input type="hidden" name="sig" value=""/>
                {/if}
            </div>
            <div class="form-group">
                <label for="inputEmail">Your Email address</label>
                <input class="form-control" type="email" id="inputEmail" name="email" required="required" value="{$currentUser->getEmail()|escape}"/>
            </div>
            <div class="form-group">
                <label for="inputEmailsig">Email signature</label>
                <textarea class="form-control col-md-11" id="inputEmailsig" rows="4" name="emailsig">{$currentUser->getEmailSig()|escape}</textarea>
                <span class="help-block">This will show up at the end of any Email you send through the interface.</span>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="inputAbortpref" name="abortpref"{if $currentUser->getAbortPref()} checked{/if}>
                <label class="form-check-label" for="inputAbortpref">Don't ask to double check before closing requests (requires Javascript)</label>
            </div>
            <button type="submit" class="btn btn-primary">Save preferences</button>
        </fieldset>
    </form>
    <div class="form-horizontal">
        <fieldset>
            <legend>Wikipedia Account</legend>

            {if $currentUser->isOAuthLinked() && $currentUser->getOnWikiName() != "##OAUTH##" }
                <div class="form-group">
                    <label for="get-oauth">Attached Wikipedia account:</label>
                    <a class="form-control" id="get-oauth" href="{$mediawikiScriptPath}?title=User:{$currentUser->getOAuthIdentity()->username|escape:'url'}">{$currentUser->getOAuthIdentity()->username|escape}</a>
                </div>
                <div class="form-group">
                    <label for="oauth-identity">Identity:</label>
                    <div class="row-fluid" id=oauth-identity>
                        <div class="col-md-4 alert-block alert{if $currentUser->getOAuthIdentity()->confirmed_email} alert-success{/if}">
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
                        <div class="col-md-4 alert-block alert{if $currentUser->getOAuthIdentity()->blocked} alert-danger{else} alert-success{/if}">
                            {if $currentUser->getOAuthIdentity()->blocked}
                                <i class="fas fa-times"></i>
                                &nbsp;
                                <strong>Blocked on Wikipedia!</strong>
                            {else}
                                <i class="fas fa-check"></i>
                                &nbsp;Not blocked.
                            {/if}
                        </div>

                        <div class="col-md-4 alert-block alert alert-success">
                            <i class="fas fa-check"></i>&nbsp;Account verified by {$currentUser->getOAuthIdentity()->iss}
                        </div>
                    </div>
                    <div class="row">
                        <div class="accordion" id="identityTicketContainer">
                            <div class="card">
                                <div class="card-header">
                                    <a class="accordion-toggle" data-toggle="collapse"
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
                <div class="form-group">
                <label for="grants-card">Grants:</label>
                    <div class="row-fluid" id="grants-card">
                        <div class="alert{if $currentUser->oauthCanUse()} alert-success{else} alert-danger{/if} col-md-4 alert-block">
                            <i class="fas fa-{if $currentUser->oauthCanUse()}check{else}times{/if}"></i>&nbsp;Basic
                            rights
                        </div>
                        {*
                          <div class="alert{if $currentUser->oauthCanEdit()} alert-success{else} alert-danger{/if} col-md-4 alert-block">
                            <i class="fas fa-{if $currentUser->oauthCanEdit()}check{else}times{/if}"></i>&nbsp;Create, edit, and move pages
                          </div>

                          <div class="alert{if $currentUser->oauthCanCreateAccount()} alert-success{else} alert-danger{/if} col-md-4 alert-block">
                            <i class="fas fa-{if $currentUser->oauthCanCreateAccount()}check{else}times{/if}"></i>&nbsp;Create accounts
                          </div>
                        *}
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">Cache:</label>
                    <div class="controls">
                        Identity ticket retrieved
                        at {DateTime::createFromFormat("U", $currentUser->getOAuthIdentity()->iat)->format("r")}, will
                        expire at {DateTime::createFromFormat("U", $currentUser->getOAuthIdentity()->exp)->format("r")}
                    </div>
                </div>

                {if !$enforceOAuth }
                    <div class="form-group">
                        <a href="{$baseurl}/internal.php/oauth/detach" class="btn btn-danger">Detach account</a>
                    </div>
                {/if}
            {else}
                <div class="form-group">
                    <label for="onwikiusername">On-wiki username</label>
                    <input disabled="disabled" class="form-control" type="text" id="onwikiusername"
                           value="{$currentUser->getOnWikiName()|escape}"/>
                </div>
                <form method="post" action="{$baseurl}/internal.php/oauth/attach">
                    {include file="security/csrf.tpl"}
                    <button type="submit" class="btn btn-success">Attach account</button>
                </form>
            {/if}
        </fieldset>
    </div>
    <form class="form-horizontal" method="post" action="{$baseurl}/internal.php/preferences/changePassword">
        {include file="security/csrf.tpl"}
        <fieldset>
            <legend>Change your password</legend>
            <div class="form-group">
                <label for="inputOldpassword">Your old password</label>
                <input class="form-control" type="password" id="inputOldpassword" name="oldpassword" required="required"/>
            </div>
            <div class="form-group">
                <label for="inputNewpassword">Your new password</label>
                <input class="form-control" type="password" id="inputNewpassword" name="newpassword" required="required"/>
            </div>
            <div class="form-group">
                <label for="inputNewpasswordconfirm">Confirm new password</label>
                <input class="form-control" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm" required="required"/>
            </div>
            <button type="submit" class="btn btn-primary">Update password</button>
        </fieldset>
    </form>
{/block}

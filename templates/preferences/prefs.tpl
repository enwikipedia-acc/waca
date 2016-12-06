{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>User preferences
            <small> Change your preferences.</small>
        </h1>
    </div>
    <form class="form-horizontal" method="post">
        {include file="security/csrf.tpl"}
        <fieldset>
            <legend>General settings</legend>
            <div class="control-group">
                {if !$currentUser->isOAuthLinked() }
                    <label class="control-label" for="inputSig">Your signature (wikicode)</label>
                    <div class="controls">
                        <input class="input-xxlarge" type="text" id="inputSig" name="sig"
                               value="{$currentUser->getWelcomeSig()|escape}"/>
                        <span class="help-block">This would be the same as ~~~ on-wiki. No date, please.</span>
                    </div>
                {else}
                    <input type="hidden" name="sig" value=""/>
                {/if}
            </div>
            <div class="control-group">
                <label class="control-label" for="inputEmail">Your Email address</label>
                <div class="controls">
                    <input class="input-xlarge" type="email" id="inputEmail" name="email" required="required"
                           value="{$currentUser->getEmail()|escape}"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputEmailsig">Email signature</label>
                <div class="controls">
                    <textarea class="field span11" id="inputEmailsig" rows="4"
                              name="emailsig">{$currentUser->getEmailSig()|escape}</textarea>
                    <span class="help-block">This will show up at the end of any Email you send through the interface.</span>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" id="inputAbortpref"
                               name="abortpref"{if $currentUser->getAbortPref()} checked{/if}> Don't ask to double check
                        before closing requests (requires Javascript)
                    </label>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-primary">Save preferences</button>
                </div>
            </div>
        </fieldset>
    </form>
    <div class="form-horizontal">
        <fieldset>
            <legend>Wikipedia Account</legend>

            {if $currentUser->isOAuthLinked() && $currentUser->getOnWikiName() != "##OAUTH##" }
                <div class="control-group">
                    <label class="control-label">Attached Wikipedia account:</label>
                    <div class="controls">
                        <a href="{$mediawikiScriptPath}?title=User:{$currentUser->getOAuthIdentity()->username|escape:'url'}">{$currentUser->getOAuthIdentity()->username|escape}</a>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Identity:</label>
                    <div class="controls">
                        <div class="row-fluid">
                            <div class="span4 alert-block alert{if $currentUser->getOAuthIdentity()->confirmed_email} alert-success{/if}">
                                {if $currentUser->getOAuthIdentity()->confirmed_email}
                                    <i class="icon-ok"></i>
                                    &nbsp;Email address confirmed
                                {else}
                                    <i class="icon-remove"></i>
                                    &nbsp;Email address
                                    <strong>NOT</strong>
                                    confirmed
                                {/if}
                            </div>
                            <div class="span4 alert-block alert{if $currentUser->getOAuthIdentity()->blocked} alert-error{else} alert-success{/if}">
                                {if $currentUser->getOAuthIdentity()->blocked}
                                    <i class="icon-remove"></i>
                                    &nbsp;
                                    <strong>Blocked on Wikipedia!</strong>
                                {else}
                                    <i class="icon-ok"></i>
                                    &nbsp;Not blocked.
                                {/if}
                            </div>

                            <div class="span4 alert-block alert alert-success">
                                <i class="icon-ok"></i>&nbsp;Account verified by {$currentUser->getOAuthIdentity()->iss}
                            </div>
                        </div>
                        <div class="row-fluid">
                            <div class="accordion" id="identityTicketContainer">
                                <div class="accordion-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse"
                                           data-parent="#identityTicketContainer" href="#identityTicketCollapseOne">
                                            Show identity ticket
                                        </a>
                                    </div>
                                    <div id="identityTicketCollapseOne" class="accordion-body collapse out">
                                        <div class="accordion-inner">
                                            <pre>{json_encode($currentUser->getOAuthIdentity(), 128)}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Grants:</label>
                    <div class="controls">
                        <div class="row-fluid">
                            <div class="alert{if $currentUser->oauthCanUse()} alert-success{else} alert-error{/if} span4 alert-block">
                                <i class="icon-{if $currentUser->oauthCanUse()}ok{else}remove{/if}"></i>&nbsp;Basic
                                rights
                            </div>
                            {*
                                      <div class="alert{if $currentUser->oauthCanEdit()} alert-success{else} alert-error{/if} span4 alert-block">
                                        <i class="icon-{if $currentUser->oauthCanEdit()}ok{else}remove{/if}"></i>&nbsp;Create, edit, and move pages
                                      </div>

                                      <div class="alert{if $currentUser->oauthCanCreateAccount()} alert-success{else} alert-error{/if} span4 alert-block">
                                        <i class="icon-{if $currentUser->oauthCanCreateAccount()}ok{else}remove{/if}"></i>&nbsp;Create accounts
                                      </div>*}
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Cache:</label>
                    <div class="controls">
                        Identity ticket retrieved
                        at {DateTime::createFromFormat("U", $currentUser->getOAuthIdentity()->iat)->format("r")}, will
                        expire at {DateTime::createFromFormat("U", $currentUser->getOAuthIdentity()->exp)->format("r")}
                    </div>
                </div>

                {if !$enforceOAuth }
                    <div class="control-group">
                        <div class="controls">
                            <a href="{$baseurl}/internal.php/oauth/detach" class="btn btn-danger">Detach account</a>
                        </div>
                    </div>
                {/if}
            {else}
                <div class="control-group">
                    <label class="control-label" for="onwikiusername">On-wiki username</label>
                    <div class="controls">
                        <input disabled="disabled" class="input-xlarge" type="text" id="onwikiusername"
                               value="{$currentUser->getOnWikiName()|escape}"/>
                    </div>
                </div>
                <form method="post" action="{$baseurl}/internal.php/oauth/attach">
                    {include file="security/csrf.tpl"}
                    <div class="control-group">
                        <div class="controls">
                            <button type="submit" class="btn btn-success">Attach account</button>
                        </div>
                    </div>
                </form>
            {/if}
        </fieldset>
    </div>
    <form class="form-horizontal" method="post" action="{$baseurl}/internal.php/preferences/changePassword">
        {include file="security/csrf.tpl"}
        <fieldset>
            <legend>Change your password</legend>
            <div class="control-group">
                <label class="control-label" for="inputOldpassword">Your old password</label>
                <div class="controls">
                    <input class="input-xlarge" type="password" id="inputOldpassword" name="oldpassword" required="required"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputNewpassword">Your new password</label>
                <div class="controls">
                    <input class="input-xlarge" type="password" id="inputNewpassword" name="newpassword" required="required"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputNewpasswordconfirm">Confirm new password</label>
                <div class="controls">
                    <input class="input-xlarge" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm" required="required"/>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-primary">Update password</button>
                </div>
            </div>
        </fieldset>
    </form>
{/block}
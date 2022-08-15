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

                    <div class="form-group row mb-5">
                        <div class="col-md-2 col-lg-3">
                            <label for="inputEmail" class="col-form-label">Your Email address</label>
                        </div>
                        <div class="col-md-8 col-lg-7 col-xl-6">
                            <input class="form-control" type="email" id="inputEmail" name="email" required="required" value="{$currentUser->getEmail()|escape}"/>
                            <small class="form-text text-muted">This is used to send you automatic notifications about events involving your account in the tool, including sending password reset emails.</small>
                        </div>
                        {include file="preferences/globalcheck.tpl" settingName="email" settingState=true settingAvailable=false}
                    </div>

                    <div class="form-group row mb-5">
                        <div class="col-md-2 col-lg-3">
                            <label for="emailSignature" class="col-form-label">Email signature</label>
                        </div>
                        <div class="col-md-8 col-lg-7 col-xl-6">
                            <textarea class="form-control" id="emailSignature" rows="5" name="emailSignature">{$emailSignature|escape}</textarea>
                            <small class="form-text text-muted">This will show up at the end of any Email you send through the interface.</small>
                        </div>
                        {include file="preferences/globalcheck.tpl" settingName="emailSignature" settingState=$emailSignatureGlobal settingAvailable=true}
                    </div>

                    <div class="form-group row mb-5">

                        <div class="col-md-2 col-lg-3">
                            <span class="col-form-label">Request closure</span>
                        </div>
                        <div class="col-md-8 col-lg-7 col-xl-6">
                            <div class="custom-control custom-switch">
                                <input class="custom-control-input" type="checkbox" id="skipJsAbort" name="skipJsAbort"{if $skipJsAbort} checked{/if}>
                                <label class="custom-control-label" for="skipJsAbort">Skip double-check prompt before closing requests</label>
                            </div>
                        </div>
                        {include file="preferences/globalcheck.tpl" settingName="skipJsAbort" settingState=$skipJsAbortGlobal settingAvailable=true}
                    </div>

                    <div class="form-group row mb-5">
                        <div class="col-md-2 col-lg-3">
                            <label class="col-form-label">Account Creation Mode</label>
                        </div>
                        <div class="col-md-8 col-lg-7 col-xl-6">

                            <div class="custom-control custom-radio">
                                <input type="radio" name="creationMode" value="0" class="custom-control-input" id="autocreateNone"
                                       {if $creationMode == 0}checked="checked"{/if}
                                       {if !$canManualCreate}disabled="disabled"{/if}/>
                                <label class="custom-control-label" for="autocreateNone">Create accounts manually using Special:CreateAccount</label>
                            </div>

                            <div class="custom-control custom-radio">
                                <input type="radio" name="creationMode" value="1" class="custom-control-input" id="autocreateOauth"
                                       {if $creationMode == 1}checked="checked"{/if}
                                       {if !$canOauthCreate}disabled="disabled"{/if}/>
                                <label class="custom-control-label" for="autocreateOauth">Use my Wikimedia account to create the accounts on my behalf where possible</label>
                                {if $canOauthCreate && !$oauth->canCreateAccount()}
                                    <span class="form-text text-danger mt-0 ml-4">You do not have the necessary grants enabled to use this option. Please check the grants you have allowed this tool from your preferences on-wiki.</span>
                                {/if}
                            </div>

                            <div class="custom-control custom-radio">
                                <input type="radio" name="creationMode" value="2" class="custom-control-input" id="autocreateBot"
                                       {if $creationMode == 2}checked="checked"{/if}
                                       {if !$canBotCreate}disabled="disabled"{/if}/>
                                <label class="custom-control-label" for="autocreateBot">Use a bot to create the accounts on my behalf where possible</label>
                            </div>

                            <small class="form-text text-muted">Please refer to the Guide for a full explanation of these options.</small>
                        </div>
                        {include file="preferences/globalcheck.tpl" settingName="creationMode" settingState=false settingAvailable=false}

                    </div>

                    <div class="form-group row mb-5">
                        <div class="col-md-2 col-lg-3">
                            <label class="col-form-label" for="skin">Tool theme</label>
                        </div>
                        <div class="col-md-6 col-lg-5 col-xl-4">
                            <select class="form-control" id="skin" name="skin">
                                <option value="auto" {if $skin === 'auto'}selected="selected"{/if}>
                                    Use browser default
                                </option>
                                <option value="main" {if $skin === 'main'}selected="selected"{/if}>
                                    Light theme
                                </option>
                                <option value="alt" {if $skin === 'alt'}selected="selected"{/if}>
                                    Dark theme
                                </option>
                            </select>
                        </div>
                        {include file="preferences/globalcheck.tpl" settingName="skin" settingState=$skinGlobal settingAvailable=true offset="offset-md-2 offset-lg-2 offset-xl-2"}
                    </div>

                    <div class="form-group row">
                        <div class="offset-md-2 offset-lg-3 col-md-4 col-lg-3">
                            <button type="submit" class="btn btn-primary btn-block">Save preferences</button>
                        </div>
                        {if count($nav__domainList) > 1}
                            <div class="offset-md-4 offset-lg-4 offset-xl-3 col-md-2 col-lg-1 d-none d-md-block">
                                <a href="#modalGlobalSettings" data-toggle="modal" class="btn btn-outline-info btn-block">Help</a>
                            </div>
                        {/if}
                    </div>

                </fieldset>
            </form>

        </div>
    </div>

    <fieldset>
        <legend>Wikipedia Account</legend>

        {if $oauth->isFullyLinked() }
            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="get-oauth" class="col-form-label">Attached Wikipedia account:</label>
                </div>
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <a href="{$mediawikiScriptPath}?title=User:{$currentUser->getOnWikiName()|escape:'url'}">{$currentUser->getOnWikiName()|escape}</a>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label for="oauth-identity">Identity:</label>
                </div>
                <div class="col-md-10 col-lg-9">
                    <div class="row row-cols-1 row-cols-xl-3 row-cols-lg-2" id="oauth-identity">
                        <div class="col">
                            <div class="alert-block alert{if $identity->getConfirmedEmail()} alert-success{/if}">
                                {if $identity->getConfirmedEmail()}
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
                            <div class="alert-block alert{if $identity->getBlocked()} alert-danger{else} alert-success{/if}">
                                {if $identity->getBlocked()}
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
                                <i class="fas fa-check"></i>&nbsp;Account verified by {$identity->getIssuer()|escape}
                            </div>
                        </div>
                        <div class="col">
                            <div class="alert-block alert alert-{if $identity->getEditCount() > 1500}success{else}danger{/if}">
                                <i class="fas fa-{if $identity->getEditCount() > 1500}check{else}times{/if}"></i>&nbsp;Edit count: {$identity->getEditCount()|escape}
                            </div>
                        </div>
                        <div class="col">
                            <div class="alert-block alert alert-{if $identity->getAccountAge() > 180}success{else}danger{/if}">
                                <i class="fas fa-{if $identity->getAccountAge() > 180}check{else}times{/if}"></i>&nbsp;Registration date: {$identity->getRegistrationDate()|escape}
                            </div>
                        </div>
                        <div class="col">
                            <div class="alert-block alert {if !$identity->getCheckuser()} alert-info{else} alert-success{/if}">
                                {if !$identity->getCheckuser()}
                                    <i class="fas fa-times"></i>&nbsp;Not a checkuser
                                {else}
                                    <i class="fas fa-check"></i>&nbsp;Checkuser
                                {/if}
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
                            <div class="alert{if $identity->getGrantBasic()} alert-success{else} alert-danger{/if} alert-block">
                                <i class="fas fa-{if $identity->getGrantBasic()}check{else}times{/if}"></i>&nbsp;Basic rights
                                <small class="form-text text-muted">
                                    This is used to verify your account, and grants no privileges. It is not possible to
                                    revoke this right and continue using this tool.
                                </small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="alert{if $identity->getGrantCreateEditMovePage()} alert-success{else} alert-danger{/if} alert-block">
                                <i class="fas fa-{if $identity->getGrantCreateEditMovePage()}check{else}times{/if}"></i>&nbsp;Create, edit, and move pages
                                <small class="form-text text-muted">
                                    This is required to create talk pages for automatic welcoming. If you want to use
                                    automatic welcoming of accounts you create, this grant is required.
                                </small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="alert{if $identity->getGrantCreateAccount()} alert-success{else} alert-danger{/if} alert-block">
                                <i class="fas fa-{if $identity->getGrantCreateAccount()}check{else}times{/if}"></i>&nbsp;Create accounts
                                <small class="form-text text-muted">
                                    This is required to create accounts through the tool. If you prefer manual creation,
                                    this grant is not required.
                                </small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="alert{if $identity->getGrantHighVolume()} alert-success{else} alert-danger{/if} alert-block">
                                <i class="fas fa-{if $identity->getGrantHighVolume()}check{else}times{/if}"></i>&nbsp;High-volume editing
                                <small class="form-text text-muted">
                                    This grant is required to bypass the 6 account creations per day limit. It is
                                    required if you want to create more than 6 accounts through the tool per 24-hour
                                    period. To avoid unnecessary failures due to missing rights, this grant is required
                                    for automatic account creations.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-2 col-lg-3">
                    <label class="control-label">Cache:</label>
                </div>
                <div class="col-md-10 col-lg-9">
                    <form method="post" action="{$baseurl}/internal.php/preferences/refreshOAuth">
                        <p>
                    Identity ticket retrieved
                        at {DateTime::createFromFormat("U", $identity->getIssuedAtTime())->format("r")}, will
                        expire at {DateTime::createFromFormat("U", $identity->getExpirationTime())->format("r")}.
                        The grace time on this token is an additional {$graceTime} beyond the expiry time.
                        </p>
                        <p>
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Refresh OAuth identity</button>
                        </p>
                    </form>
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

    {if count($nav__domainList) > 1}
        {include file="preferences/globalhelp.tpl"}
    {/if}
{/block}

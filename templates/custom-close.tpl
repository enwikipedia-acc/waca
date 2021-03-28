{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Custom close #{$requestId}{if $preloadTitle != ""}<small class="text-muted"> {$preloadTitle|escape}</small>{/if}</h1>
            </div>
        </div>
    </div>

    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="form-group row">
            <div class="d-none d-lg-block col-lg-3 col-xl-2">
                <label class="col-form-label">Request details:</label>
            </div>
            <div class="col-lg-6 padded-data">
                {include file="view-request/request-info.tpl"}
            </div>
        </div>
        <div class="form-group row mt-4">
            <div class="col-lg-9 offset-lg-3 col-xl-10 offset-xl-2">
                {include file="alert.tpl" alertblock="1" alerttype="alert-danger" alertclosable="0" alertheader="Caution!"
                alertmessage="The contents of this box will be sent as an email to the user with the signature set in <a href=\"{$baseurl}/internal.php/preferences\">your preferences</a> appended to it. <strong>If you do not set a signature in your preferences, please manually enter one at the end of your message</strong>."}
            </div>
        </div>
        <div class="form-group row">
            <div class="col-lg-3 col-xl-2">
                <label class="col-form-label" for="msgbody">Message to be sent to the user:</label>
            </div>
            <div class="col-lg-9 col-xl-10">
                <textarea id="msgbody" name="msgbody" rows="15" class="form-control" required="required">{$preloadText|escape}</textarea>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-lg-3 col-xl-2">
                <label for="inputAction">Action to take</label>
            </div>
            <div class="col-md-8 col-lg-6">
                <select class="form-control" id="inputAction" name="action" required="required">
                    <option value="" {if $defaultAction == ""}selected="selected"{/if}>(please select)</option>
                    <option value="mail" {if $preloadAction == "mail"}selected="selected"{/if}>Only send the email</option>
                    <optgroup label="Send email and close request...">
                        <option value="created" {if $preloadAction == "created" || ($preloadAction === null && $defaultAction == "created" && $currentUser->getCreationMode() == 0)}selected="selected"{/if}>
                            Close request as created
                        </option>
                        {if $canOauthCreate}
                            <option value="{Waca\Pages\RequestAction\PageCustomClose::CREATE_OAUTH}"  {if $preloadAction == Waca\Pages\RequestAction\PageCustomClose::CREATE_OAUTH || ($preloadAction === null && $defaultAction == "created" && $currentUser->getCreationMode() == 1)}selected="selected"{/if}>
                                Create account (Wikimedia account) & close request as created
                            </option>
                        {/if}
                        {if $canBotCreate}
                            <option value="{Waca\Pages\RequestAction\PageCustomClose::CREATE_BOT}"  {if $preloadAction == Waca\Pages\RequestAction\PageCustomClose::CREATE_BOT || ($preloadAction === null && $defaultAction == "created" && $currentUser->getCreationMode() == 2)}selected="selected"{/if}>
                                Create account (via bot) & close request as created
                            </option>
                        {/if}
                        <option value="not created" {if $preloadAction == "not created" || ($preloadAction === null && $defaultAction == "not created") }selected="selected"{/if}>
                            Close request as NOT created
                        </option>
                    </optgroup>
                    <optgroup label="Send email and defer to...">
                        {foreach $requeststates as $state}
                            <option value="{$state@key}" {if $preloadAction == $state@key || ($preloadAction === null && $defaultAction == $state@key )}selected="selected"{/if}>
                                Defer to {$state.deferto|capitalize}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-lg-3 offset-xl-2 col">
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" id="ccMailingList" name="ccMailingList" {if $ccMailingList === null || $ccMailingList}checked="checked"{/if} {if !$canSkipCcMailingList}disabled="disabled"{/if}/>
                    <label class="custom-control-label" for="ccMailingList">CC to mailing list</label>
                </div>
            </div>
        </div>

        {if $confirmEmailAlreadySent}
        <div class="form-group row">
            <div class="offset-lg-3 offset-xl-2 col">
                <div class="alert alert-warning alert-block mb-0">
                    <p>This request has already had an email sent. Please acknowledge that your message is context-aware of the earlier message.</p>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="confirmEmailAlreadySent" name="confirmEmailAlreadySent" required="required"/>
                        <label class="custom-control-label" for="confirmEmailAlreadySent">Yes, this is an appropriate follow-up email</label>
                    </div>
                </div>
            </div>
        </div>
        {else}
            <input type="hidden" name="confirmEmailAlreadySent" value="true" />
        {/if}

        {if $allowWelcomeSkip}
            <div class="form-group row">
                <div class="offset-lg-3 offset-xl-2 col">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="skipAutoWelcome" id="skipAutoWelcome" class="custom-control-input" {if $forceWelcomeSkip}disabled="disabled" checked="checked"{else}{if $skipAutoWelcome}checked="checked"{/if}{/if} />
                        <label for="skipAutoWelcome" class="custom-control-label">Skip automatic welcome on account creation</label>
                        {if $forceWelcomeSkip}
                            <input type="hidden" name="skipAutoWelcome" value="true" />
                        {/if}
                    </div>
                </div>
            </div>
        {/if}

        {if $showNonExistentAccountWarning}
            <div class="form-group row">
                <div class="offset-lg-3 offset-xl-2 col">
                    <div class="alert alert-warning alert-block mb-0">
                        <p>You have chosen to mark this request as "created", but the account does not exist on the English
                            Wikipedia and you have not selected an auto-creation option. Do you wish to proceed?</p>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="createOverride" id="createOverride" class="custom-control-input" required />
                            <label for="createOverride" class="custom-control-label">Yes, proceed with marking this request as created</label>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        <input type="hidden" name="updateversion" value="{$updateVersion}"/>

        <div class="form-group row">
            <div class="offset-lg-3 offset-xl-2 col-md-9 col-lg-5 col-xl-4">
                <button type="submit" class="btn btn-primary btn-block" name="submit">
                    <i class="fas fa-check-circle"></i>&nbsp;Close and send
                </button>
            </div>
        </div>
    </form>
{/block}

{include file="view-request/request-private-data.tpl"}

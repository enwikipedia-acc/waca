{extends file="pagebase.tpl"}
{block name="content"}
    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="row">
            <div class="col-12">
                <legend>Custom close{if $preloadTitle != ""} - {$preloadTitle|escape}{/if}</legend>
            </div>
        </div>
        <div class="form-group row">
            <div class="d-none d-lg-block col-lg-3 col-xl-2">
                <label class="col-form-label">Request details:</label>
            </div>
            <div class="col-lg-6">
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
            <div class="col-md-5 col-lg-4">
                <select class="form-control" id="inputAction" name="action" required="required">
                    <option value="" {if $defaultAction == ""}selected="selected"{/if}>(please select)</option>
                    <option value="mail">Only send the email</option>
                    <optgroup label="Send email and close request...">
                        <option value="created" {if $defaultAction == "created"}selected="selected"{/if}>Close
                            request as created
                        </option>
                        <option value="not created" {if $defaultAction == "not created"}selected="selected"{/if}>
                            Close request as NOT created
                        </option>
                    </optgroup>
                    <optgroup label="Send email and defer to...">
                        {foreach $requeststates as $state}
                            <option value="{$state@key}" {if $defaultAction == $state@key}selected="selected"{/if}>
                                Defer to {$state.deferto|capitalize}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-lg-3 offset-xl-2 col">
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" id="ccMailingList" name="ccMailingList" checked="checked" {if !$canSkipCcMailingList}disabled="disabled"{/if}/>
                    <label class="custom-control-label" for="ccMailingList">CC to mailing list</label>
                </div>
            </div>
        </div>

        {if $confirmEmailAlreadySent}
        <div class="form-group row">
            <div class="offset-lg-3 offset-xl-2 col">
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" id="confirmEmailAlreadySent" name="confirmEmailAlreadySent" required="required"/>
                    <label class="custom-control-label" for="confirmEmailAlreadySent">Override email already sent check</label>
                </div>
            </div>
        </div>
        {else}
            <input type="hidden" name="confirmEmailAlreadySent" value="true" />
        {/if}

        {if $confirmReserveOverride}
        <div class="form-group row">
            <div class="offset-lg-3 offset-xl-2 col">
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" id="confirmReserveOverride" name="confirmReserveOverride" required="required"/>
                    <label class="custom-control-label" for="confirmReserveOverride">Override reservation on this request by {$requestReservedByName|escape}?</label>
                </div>
            </div>
        </div>
        {else}
            <input type="hidden" name="confirmReserveOverride" value="true" />
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

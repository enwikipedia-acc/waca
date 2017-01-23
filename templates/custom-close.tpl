{extends file="pagebase.tpl"}
{block name="content"}
    <form method="post" class="form-horizontal">
        {include file="security/csrf.tpl"}
        <fieldset>
            <legend>Custom close{if $preloadTitle != ""} - {$preloadTitle|escape}{/if}</legend>

            <div class="control-group">
                <label class="control-label">Request details:</label>
                <div class="controls">
                    <div class="container-fluid">
                        <div class="row-fluid">
                            <div class="span6">
                                {include file="view-request/request-info.tpl"}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="control-group">
                <label for="msgbody" class="control-label">Message to be sent to the user:</label>
                <div class="controls">
                    {include file="alert.tpl" alertblock="1" alerttype="alert-error" alertclosable="0" alertheader="Caution!"
                    alertmessage="The contents of this box will be sent as an email to the user with the signature set in <a href=\"{$baseurl}/internal.php/preferences\">your preferences</a> appended to it. <strong>If you do not set a signature in your preferences, please manually enter one at the end of your message</strong>."}
                    <textarea id="msgbody" name="msgbody" rows="15" class="input-block-level"
                              required="required">{$preloadText|escape}</textarea>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="inputAction">Action to take</label>
                <div class="controls">
                    <select class="input-xlarge" id="inputAction" name="action" required="required">
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

            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" name="ccMailingList" checked="checked"
                               {if !$canSkipCcMailingList}disabled="disabled"{/if}
                        />
                        CC to mailing list
                    </label>
                </div>
            </div>

            {if $confirmEmailAlreadySent}
                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" name="confirmEmailAlreadySent" required="required" />
                            Override email already sent check
                        </label>
                    </div>
                </div>
            {else}
                <input type="hidden" name="confirmEmailAlreadySent" value="true" />
            {/if}


            {if $confirmReserveOverride}
                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" name="confirmReserveOverride" required="required" />
                            Override reservation on this request by {$requestReservedByName|escape}?
                        </label>
                    </div>
                </div>
            {else}
                <input type="hidden" name="confirmReserveOverride" value="true" />
            {/if}

            <input type="hidden" name="updateversion" value="{$updateVersion}"/>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Close and send</button>
                <a href="{$baseurl}/internal.php/viewRequest?id={$requestId}" class="btn">Cancel</a>
            </div>
        </fieldset>
    </form>
{/block}

{include file="view-request/request-private-data.tpl"}

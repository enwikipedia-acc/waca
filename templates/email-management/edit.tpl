{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>
            Email Management
            <small>
                Edit Email template
            </small>
        </h1>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" method="post">
                {include file="security/csrf.tpl"}
                <div class="form-group">
                    <label for="inputName">Email template name</label>
                    <input class="form-control" type="text" id="inputName" name="name" required="required"
                             value="{$emailTemplate->getName()|escape}"/>
                    <span class="help-block">The name of the Email template. Note that this will be used to label the relevant close button on the request zoom pages.</span>
                </div>

                <div class="form-group">
                    <label for="inputText">Email text</label>
                    <textarea class="form-control" id="inputText" rows="20" required="required"
                              name="text">{$emailTemplate->getText()|escape}</textarea>
                    <span class="help-block">The text of the Email which will be sent to the requesting user.</span>
                </div>

                <div class="form-group">
                    <label for="inputQuestion">JavaScript question</label>
                    <input type="text" class="form-control" id="inputQuestion" name="jsquestion" size="75"
                           value="{$emailTemplate->getJsquestion()|escape}"/>
                    <span class="help-block">Text to appear in a JavaScript popup (if enabled by the user) when they attempt to use this Email template.</span>
                </div>

                <div class="form-group">
                    <label  for="inputDefaultAction">Default action</label>
                    {if $id == $createdid}
                        {include file="alert.tpl" alertblock=false alerttype="alert-info" alertclosable=false alertheader="" alertmessage="This is the default close template, and cannot be disabled or unmarked as a close template."}
                    {/if}

                    <select class="form-control" id="inputDefaultAction"
                            name="defaultaction" {if $id == $createdid} disabled{/if}>
                        <option value="" {if $emailTemplate->getDefaultAction() == ""}selected="selected"{/if}>No
                            default
                        </option>
                        <optgroup label="Close request...">
                            <option value="created"
                                    {if $emailTemplate->getDefaultAction() == "created"}selected="selected"{/if}>
                                Close request as created
                            </option>
                            <option value="not created"
                                    {if $emailTemplate->getDefaultAction() == "not created"}selected="selected"{/if}>
                                Close request as NOT created
                            </option>
                        </optgroup>
                        <optgroup label="Defer to...">
                            {foreach $requeststates as $state}
                                <option value="{$state@key}"
                                        {if $emailTemplate->getDefaultAction() == $state@key}selected="selected"{/if}>
                                    Defer to {$state.deferto|capitalize}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                    <span class="help-block">The default action to take on custom close. This is also used for populating decline and created dropdowns</span>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="inputActive" name="active" {if $id == $createdid} disabled{/if}{if {$emailTemplate->getActive()}} checked{/if}/>
                        <label class="form-check-label" for="active">Enabled</label>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="inputPreloadonly" name="preloadonly"{if $id == $createdid} disabled{/if}{if {$emailTemplate->getPreloadOnly()}} checked{/if} />
                    <label class="form-check-label" for="preloadonly">Available for preload only</label>
                </div>

                <input type="hidden" name="updateversion" value="{$emailTemplate->getUpdateVersion()}" />

                <div class="form-actions">
                    <a class="btn" href="{$baseurl}/internal.php/emailManagement">Cancel</a>
                    <button type="submit" class="btn btn-primary" name="submit">
                        <i class="fas fa-check-circle"></i>&nbsp;Save
                    </button>
                </div>

            </form>
        </div>
    </div>
{/block}

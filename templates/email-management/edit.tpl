{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Email Management <small class="text-muted">Create and edit close reasons</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="form-group row">
                    <div class="col-sm-3 col-xl-2">
                        <label for="inputName" class="col-form-label">Email template name</label>
                    </div>
                    <div class="col-sm-9 col-md-5 col-lg-5">
                        <input class="form-control" type="text" id="inputName" name="name" required="required"
                               value="{$emailTemplate->getName()|escape}" aria-describedby="templateNameHelp" />
                        <small class="form-text text-muted" id="templateNameHelp">The name of the Email template. Note that this will be used to label the relevant close button on the request zoom pages.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-3 col-xl-2">
                        <label for="inputText" class="col-form-label">Email text</label>
                    </div>
                    <div class="col-md-9 col-xl-10">
                        <textarea class="form-control" id="inputText" rows="20" required="required" name="text" aria-describedby="templateTextHelp">{$emailTemplate->getText()|escape}</textarea>
                        <small class="form-text text-muted" id="templateTextHelp">The text of the Email which will be sent to the requesting user.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-3 col-xl-2">
                        <label for="inputQuestion" class="col-form-label">JavaScript question</label>
                    </div>
                    <div class="col-md-9 col-xl-10">
                        <input type="text" class="form-control" id="inputQuestion" name="jsquestion" required="required" value="{$emailTemplate->getJsquestion()|escape}" aria-describedby="templateJsQuestion"/>
                        <small class="form-text text-muted" id="templateJsQuestion">Text to appear in a JavaScript popup (if enabled by the user) when they attempt to use this Email template.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-3 col-xl-2">
                        <label class="col-form-label" for="inputDefaultAction">Default action</label>
                    </div>
                    <div class="col-sm-9 col-lg-5 col-xl-4">
                        {if $id == $createdid}
                            {include file="alert.tpl" alertblock=false alerttype="alert-info" alertclosable=false alertheader="" alertmessage="This is the default close template, and cannot be disabled or unmarked as a close template."}
                        {/if}

                        <select class="form-control" id="inputDefaultAction" aria-describedby="templateDefaultActionHelp"
                                name="defaultaction" {if $id == $createdid} disabled{/if}>
                            <option value="" {if $emailTemplate->getDefaultAction() == ""}selected="selected"{/if}>
                                No default
                            </option>
                            <optgroup label="Close request...">
                                <option value="created" {if $emailTemplate->getDefaultAction() == "created"}selected="selected"{/if}>
                                    Close request as created (with autocreate if allowed)
                                </option>
                                <option value="not created" {if $emailTemplate->getDefaultAction() == "not created"}selected="selected"{/if}>
                                    Close request as NOT created
                                </option>
                            </optgroup>
                            <optgroup label="Defer to...">
                                {foreach $requeststates as $state}
                                    <option value="{$state@key}" {if $emailTemplate->getDefaultAction() == $state@key}selected="selected"{/if}>
                                        Defer to {$state.deferto|capitalize}
                                    </option>
                                {/foreach}
                            </optgroup>
                        </select>
                        <small class="form-text text-muted" id="templateDefaultActionHelp">The default action to take on custom close. This is also used for populating decline and created dropdowns</small>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="offset-md-3 offset-xl-2 col-md-9">
                        <div class="custom-control custom-switch">
                            <input class="custom-control-input" type="checkbox" id="inputActive" name="active" {if $id == $createdid} disabled{/if}{if {$emailTemplate->getActive()}} checked{/if}/>
                            <label class="custom-control-label" for="inputActive">Enabled</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="offset-md-3 offset-xl-2 col-md-9">
                        <div class="custom-control custom-switch">
                            <input class="custom-control-input" type="checkbox" id="inputPreloadonly" name="preloadonly"{if $id == $createdid} disabled{/if}{if {$emailTemplate->getPreloadOnly()}} checked{/if} />
                            <label class="custom-control-label" for="inputPreloadonly">Available for preload only</label>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="updateversion" value="{$emailTemplate->getUpdateVersion()}" />

                <div class="form-group row">
                    <div class="offset-md-3 offset-xl-2 col-md-9 col-lg-5 col-xl-4">
                        <button type="submit" class="btn btn-primary btn-block" name="submit">
                            <i class="fas fa-check-circle"></i>&nbsp;Save
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
{/block}

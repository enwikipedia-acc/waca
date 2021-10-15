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
            <div class="form-group row">
                <div class="col-sm-3 col-xl-2">
                    <label for="inputName" class="col-form-label">Email template name</label>
                </div>
                <div class="col-sm-9 col-md-5 col-lg-5">
                    <input class="form-control" type="text" id="inputName" name="name" required="required"
                           value="{$emailTemplate->getName()|escape}" aria-describedby="templateNameHelp" readonly />
                    <small class="form-text text-muted" id="templateNameHelp">The name of the Email template. Note that this will be used to label the relevant close button on the request zoom pages.</small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-3 col-xl-2">
                    <label for="inputText" class="col-form-label">Email text</label>
                </div>
                <div class="col-md-9 col-xl-10">
                    <textarea class="form-control" id="inputText" rows="20" required="required" name="text" readonly aria-describedby="templateTextHelp">{$emailTemplate->getText()|escape}</textarea>
                    <small class="form-text text-muted" id="templateTextHelp">The text of the Email which will be sent to the requesting user.</small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-3 col-xl-2">
                    <label for="inputQuestion" class="col-form-label">JavaScript question</label>
                </div>
                <div class="col-md-9 col-xl-10">
                    <input type="text" class="form-control" id="inputQuestion" name="jsquestion" readonly value="{$emailTemplate->getJsquestion()|escape}" aria-describedby="templateJsQuestion"/>
                    <small class="form-text text-muted" id="templateJsQuestion">Text to appear in a JavaScript popup (if enabled by the user) when they attempt to use this Email template.</small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-3 col-xl-2">
                    <label class="col-form-label" for="inputDefaultAction">Default action</label>
                </div>
                <div class="col-sm-9 col-lg-5 col-xl-4 lead">
                    {if $emailTemplate->getDefaultAction() == Waca\DataObjects\EmailTemplate::ACTION_NONE}
                        <span class="badge badge-secondary">No default action</span>
                    {elseif $emailTemplate->getDefaultAction() == Waca\DataObjects\EmailTemplate::ACTION_CREATED}
                        <span class="badge badge-success">Close request as created</span>
                    {elseif $emailTemplate->getDefaultAction() == Waca\DataObjects\EmailTemplate::ACTION_NOT_CREATED}
                        <span class="badge badge-danger">Close request as NOT created</span>
                    {elseif $emailTemplate->getDefaultAction() == Waca\DataObjects\EmailTemplate::ACTION_DEFER}
                        <span class="badge badge-info">Defer to {$emailTemplate->getQueueObject()->getDisplayName()|escape}</span>
                    {/if}
                </div>
            </div>

            <div class="form-group row">
                <div class="offset-md-3 offset-xl-2 col-md-9">
                    <div class="custom-control custom-switch">
                        <input class="custom-control-input" type="checkbox" id="inputActive" name="active" disabled{if {$emailTemplate->getActive()}} checked{/if}/>
                        <label class="custom-control-label" for="inputActive">Enabled</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="offset-md-3 offset-xl-2 col-md-9">
                    <div class="custom-control custom-switch">
                        <input class="custom-control-input" type="checkbox" id="inputPreloadonly" name="preloadonly" disabled{if {$emailTemplate->getPreloadOnly()}} checked{/if} />
                        <label class="custom-control-label" for="inputPreloadonly">Available for preload only</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}

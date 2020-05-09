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

            <div class="form-group">
                <label for="inputName">Email template name</label>
                <input class="form-control" type="text" id="inputName" name="name" value="{$emailTemplate->getName()|escape}"
                       disabled="disabled"/>
            </div>

            <div class="form-group">
                <label for="inputText">Email text</label>
                <div class="card card-body">
                    <div class="prewrap">{$emailTemplate->getText()|escape}</div>
                </div>
            </div>

            <div class="form-group">
                <label for="inputQuestion">JavaScript popup question</label>
                <div class="card card-body">
                    <div class="prewrap">{$emailTemplate->getJsquestion()|escape}</div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label" for="inputDefaultAction">Default action:</label>

                    {if $emailTemplate->getDefaultAction() == ""}
                        <span class="badge badge-secondary">No default action</span>
                    {elseif $emailTemplate->getDefaultAction() == "created"}
                        <span class="badge badge-success">Close request as created</span>
                    {elseif $emailTemplate->getDefaultAction() == "not created"}
                        <span class="badge badge-danger">Close request as NOT created</span>
                    {else}
                        <span class="badge badge-info">Defer to {$requeststates[$emailTemplate->getDefaultAction()].deferto|capitalize}</span>
                    {/if}

            </div>

            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" id="inputActive" name="active"
                               disabled="disabled" {if {$emailTemplate->getActive()}} checked{/if} />
                        Enabled
                    </label>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" id="inputPreloadonly" name="preloadonly"
                               disabled="disabled" {if {$emailTemplate->getPreloadOnly()}} checked{/if} />
                        Available for preload only
                    </label>
                </div>
            </div>
        </div>
    </div>
{/block}

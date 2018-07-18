{extends file="pagebase.tpl"}
{block name="content"}
    <div class="jumbotron">
      <h1>Email Management</h1>
        <p>View email template</p>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-horizontal">
                <div class="form-group">
                    <label for="inputName">Email template name</label>
                    <input class="form-control" type="text" id="inputName" name="name" value="{$emailTemplate->getName()|escape}"
                           disabled="disabled"/>
                </div>

                <div class="form-group">
                    <label for="inputText">Email text</label>
                    <div class="form-control">
                        <pre>{$emailTemplate->getText()|escape}</pre>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputQuestion">JavaScript popup question</label>
                    <div class="form-control">
                        <div class="card card-body">{$emailTemplate->getJsquestion()|escape}</div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="inputDefaultAction">Default action</label>
                    <div class="controls">
                        {if $emailTemplate->getDefaultAction() == ""}
                            No default action
                        {elseif $emailTemplate->getDefaultAction() == "created"}
                            Close request as created
                        {elseif $emailTemplate->getDefaultAction() == "not created"}
                            Close request as NOT created
                        {else}
                            Defer to {$requeststates[$emailTemplate->getDefaultAction()].deferto|capitalize}
                        {/if}
                    </div>
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
    </div>
{/block}

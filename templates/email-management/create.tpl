{extends file="base.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>
            Email Management
            <small>
                Create Email template
            </small>
        </h1>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <form class="form-horizontal" method="post">

                <div class="control-group">
                    <label class="control-label" for="inputName">Email template name</label>
                    <div class="controls">
                        <input type="text" id="inputName" name="name" required="required"/>
                        <span class="help-block">The name of the Email template. Note that this will be used to label the relevant close button on the request zoom pages.</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="inputText">Email text</label>
                    <div class="controls">
                        <textarea class="input-xxlarge" id="inputText" rows="20" name="text"
                                  required="required"></textarea>
                        <span class="help-block">The text of the Email which will be sent to the requesting user.</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="inputQuestion">JavaScript question</label>
                    <div class="controls">
                        <input type="text" class="input-xxlarge" id="inputQuestion" name="jsquestion" size="75"/>
                        <span class="help-block">Text to appear in a JavaScript popup (if enabled by the user) when they attempt to use this Email template.</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="inputDefaultAction">Default action</label>
                    <div class="controls">
                        <select class="input-xlarge" id="inputDefaultAction" name="defaultaction">
                            <option value="" selected="selected">No
                                default
                            </option>
                            <optgroup label="Close request...">
                                <option value="created">
                                    Close request as created
                                </option>
                                <option value="not created">
                                    Close request as NOT created
                                </option>
                            </optgroup>
                            <optgroup label="Defer to...">
                                {foreach $requeststates as $state}
                                    <option value="{$state@key}">
                                        Defer to {$state.deferto|capitalize}</option>
                                {/foreach}
                            </optgroup>
                        </select>
                        <span class="help-block">The default action to take on custom close. This is also used for populating decline and created dropdowns</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" id="inputActive" name="active"/>
                            Enabled
                        </label>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" id="inputPreloadonly" name="preloadonly"/>
                            Available for preload only
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <a class="btn" href="{$baseurl}/internal.php/emailManagement">Cancel</a>
                    <button type="submit" class="btn btn-primary" name="submit">
                        <i class="icon-white icon-ok"></i>&nbsp;Save
                    </button>
                </div>

            </form>
        </div>
    </div>
{/block}
{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Request Form Management <small class="text-muted">Create and edit request forms</small></h1>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <form method="post">
                {include file="security/csrf.tpl"}

                <fieldset>
                    <legend>Form metadata</legend>
                    <div class="row">
                        <div class="col-lg-6 col-xs-12">
                            <div class="form-group row">
                                <div class="col-md-3 col-lg-4">
                                    <label for="name" class="col-form-label">Form name:</label>
                                </div>
                                <div class="col-md-6 col-lg-8 col-xl-6">
                                    <input type="text" class="form-control" id="name" name="name" maxlength="255" required="required" placeholder="Main form" value="{$name|escape}"/>
                                    <small class="form-text text-muted" id="nameHelp">The name of this form.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group row">
                                <div class="offset-md-3 offset-lg-3 col-md-9">
                                    <div class="custom-control custom-switch">
                                        <input class="custom-control-input" type="checkbox" id="enabled" name="enabled" {if $enabled}checked{/if} />
                                        <label class="custom-control-label" for="enabled">Enabled</label>
                                        <small class="form-text text-muted" id="defaultHelp">Allow this form to be used for new requests.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group row">
                                <div class="col-md-3 col-lg-2">
                                    <label for="endpoint" class="col-form-label">Public endpoint:</label>
                                </div>
                                <div class="col-md-9 col-lg-7 col-xl-5">
                                    {if $createMode}
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">{$baseurl|escape}/index.php/r/{$currentDomain->getShortName()|escape}/</div>
                                        </div>
                                        <input type="text" class="form-control" id="endpoint" name="endpoint" maxlength="64" required="required" pattern="^[A-Za-z][a-zA-Z0-9-]*$" placeholder="default-form" value="{$endpoint|escape}"  {if !$createMode}disabled{/if}/>
                                    </div>
                                    {else}
                                        <span class="form-control"><a href="{$baseurl|escape}/index.php/r/{$domain->getShortName()|escape}/{$endpoint|escape}">{$baseurl|escape}/index.php/r/{$domain->getShortName()|escape}/{$endpoint|escape}</a></span>
                                    {/if}

                                    <small class="form-text text-muted" id="displayNameHelp">The public URL of the form. Cannot be changed after creation. Must start with a letter, and only contain letters, numbers, hyphens and underscores.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group row">
                                <div class="col-md-3 col-lg-4">
                                    <label for="queue" class="col-form-label">Override destination queue</label>
                                </div>
                                <div class="col-md-6 col-lg-8 col-xl-6">
                                    <select class="form-control" name="queue" id="queue">
                                        <option value="" {if null == $queue}selected="selected"{/if}>(Use default queue)</option>
                                        {foreach $availableQueues as $q}
                                            <option value="{$q->getId()|escape}" {if $q->getId() == $queue}selected="selected"{/if}>{$q->getHeader()|escape}</option>
                                        {/foreach}
                                    </select>

                                    <small class="form-text text-muted" id="queueHelp">Choose an alternate default queue to direct requests from this form into.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </fieldset>
                <fieldset>
                    <legend>Form content</legend>
                    <p>Formatting in these fields is supported using Markdown syntax.</p>

                    <div class="row">
                        <div class="col">
                            <div class="form-group row">
                                <div class="col-md-3 col-lg-2">
                                    <label for="content" class="col-form-label">Preamble:</label>
                                </div>
                                <div class="col-md-9 col-lg-10">
                                    <textarea class="form-control" id="content" rows="5" name="content" required="required">{$content|escape}</textarea>
                                    <small class="form-text text-muted" id="contentHelp">The text displayed before the request form.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group row">
                                <div class="col-md-3 col-lg-2">
                                    <label for="username" class="col-form-label">Username help:</label>
                                </div>
                                <div class="col-md-9 col-lg-10">
                                    <input type="text" class="form-control" id="username" required="required" name="username" value="{$username|escape}"/>
                                    <small class="form-text text-muted" id="usernameHelp">The text displayed underneath the username field.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group row">
                                <div class="col-md-3 col-lg-2">
                                    <label for="email" class="col-form-label">Email address help:</label>
                                </div>
                                <div class="col-md-9 col-lg-10">
                                    <input type="text" class="form-control" id="email" required="required" name="email" value="{$email|escape}"/>
                                    <small class="form-text text-muted" id="emailHelp">The text displayed underneath the email address fields.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group row">
                                <div class="col-md-3 col-lg-2">
                                    <label for="email" class="col-form-label">Comments help:</label>
                                </div>
                                <div class="col-md-9 col-lg-10">
                                    <input type="text" class="form-control" id="comment" required="required" name="comment" value="{$comment|escape}"/>
                                    <small class="form-text text-muted" id="commentHelp">The text displayed underneath the comment field.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                {if !isset($hidePreview)}

                <fieldset>
                    <legend>Preview</legend>
                    <div class="row">
                        <div class="col-12">
                            <iframe src="{$baseurl}/internal.php/requestFormManagement/preview" class="preview-frame preview-frame-short"></iframe>
                        </div>
                    </div>
                </fieldset>
                {/if}

                <div class="row">
                    <div class="col">
                        <div class="form-group row">
                            <div class="offset-md-3 offset-lg-2 col-md-6 col-lg-4 col-xl-3">
                                <button type="submit" class="btn btn-block btn-primary"><i class="fas fa-save"></i>&nbsp;Save</button>
                            </div>
                            <div class="col-md-3 col-lg-3 col-xl-2">
                                <button type="submit" name="preview" value="preview" class="btn btn-block btn-secondary"><i class="fas fa-eye"></i>&nbsp;Preview</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/block}
{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Domain Management <small class="text-muted">Manage security domain settings</small></h1>
                {if $canCreate && $canEditAll}
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a class="btn btn-sm btn-outline-success"
                           href="{$baseurl}/internal.php/domainManagement/create"><i class="fas fa-plus"></i>&nbsp;Create
                            domain</a>
                    </div>
                {/if}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="row">
                    <div class="col-lg-6 col-xs-12">
                        <fieldset>
                            <legend>Wiki instance</legend>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="shortName" class="col-form-label">Short name:</label>
                                </div>
                                <div class="col-sm-5 col-md-4 col-lg-6 col-xl-4">
                                    <input type="text" class="form-control" id="shortName" name="shortName" maxlength="20" required="required" placeholder="enwiki" value="{$shortName|escape}" {if !$createMode}readonly{/if}/>
                                    <small class="form-text text-muted" id="shortNameHelp">The short "database" name of the wiki. <strong>Note: this cannot be changed after creation.</strong></small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="longName" class="col-form-label">Long name:</label>
                                </div>
                                <div class="col-sm-8 col-md-6 col-lg-8 col-xl-6">
                                    <input type="text" class="form-control" id="longName" name="longName" maxlength="255" required="required" placeholder="English Wikipedia" value="{$longName|escape}"/>
                                    <small class="form-text text-muted" id="longNameHelp">The full name of the wiki</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="articlePath" class="col-form-label">Article path:</label>
                                </div>
                                <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                    <input type="text" class="form-control" id="articlePath" name="articlePath" maxlength="255" required="required" placeholder="https://en.wikipedia.org/w/index.php" value="{$articlePath|escape}" {if !$canEditAll}readonly{/if}/>
                                    <small class="form-text text-muted" id="articlePathHelp">The base path of an article</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="apiPath" class="col-form-label">API path:</label>
                                </div>
                                <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                    <input type="text" class="form-control" id="apiPath" name="apiPath" maxlength="255" required="required" placeholder="https://en.wikipedia.org/w/api.php" value="{$apiPath|escape}" {if !$canEditAll}readonly{/if}/>
                                    <small class="form-text text-muted" id="apiPathHelp">The path to the API</small>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-lg-6 col-xs-12">
                        <fieldset>
                            <legend>Domain settings</legend>

                            <div class="form-group row">
                                <div class="offset-sm-4 offset-md-3 offset-lg-4 offset-xl-3 col-md-9">
                                    <div class="custom-control custom-switch">
                                        <input class="custom-control-input" type="checkbox" id="enabled" name="enabled" {if $enabled}checked{/if}  {if (!$canEditAll)}disabled{/if}/>
                                        <label class="custom-control-label" for="enabled">Enabled</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="defaultClose" class="col-form-label">Default creation template:</label>
                                </div>
                                <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                    {if $createMode}
                                        <div class="alert alert-warning">
                                            This setting cannot be set at domain creation. Please create the domain, then
                                            configure some email templates within the domain, and finally return here to set
                                            the default creation template.
                                        </div>
                                    {else}
                                        <select class="form-control" name="defaultClose" id="defaultClose">
                                            {foreach $closeTemplates as $template}
                                                <option value="{$template->getId()}" {if $defaultClose == $template->getId()}selected="selected"{/if}>{$template->getName()|escape}</option>
                                            {/foreach}
                                        </select>
                                    {/if}
                                    <small class="form-text text-muted" id="defaultCloseHelp">The default creation template to use</small>

                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="defaultLanguage" class="col-form-label">Default language:</label>
                                </div>
                                <div class="col-sm-3 col-md-3 col-lg-4 col-xl-3">
                                    <input type="text" class="form-control" id="defaultLanguage" name="defaultLanguage" maxlength="10" required="required" placeholder="en" value="{$defaultLanguage|escape}"/>
                                    <small class="form-text text-muted" id="defaultLanguageHelp">The default language for new users</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="emailSender" class="col-form-label">Email sender address:</label>
                                </div>
                                <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                    <input type="email" class="form-control" id="emailSender" name="emailSender" maxlength="255" required="required" placeholder="accounts-enwiki-l@lists.wikimedia.org" value="{$emailSender|escape}" {if !$canEditAll}readonly{/if}/>
                                    <small class="form-text text-muted" id="emailSenderHelp">The email address to send emails from. This is also used as a Reply-To address.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="notificationTarget" class="col-form-label">IRC notification target:</label>
                                </div>
                                <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                    <input type="text" class="form-control" id="notificationTarget" name="notificationTarget" maxlength="255" placeholder="" value="{$notificationTarget|escape}" {if !$canEditAll}readonly{/if}/>
                                    <small class="form-text text-muted" id="notificationTargetHelp">The target to send IRC notifications to via Helpmebot.</small>
                                </div>
                            </div>


                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="localDocumentation" class="col-form-label">Tool documentation URL:</label>
                                </div>
                                <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                    <input type="text" class="form-control" id="localDocumentation" name="localDocumentation" maxlength="255" required="required" placeholder="e.g. https://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide" value="{$localDocumentation|escape}"/>
                                    <small class="form-text text-muted" id="localDocumentationHelp">The URL of the page containing the local tool documentation</small>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-xs-12">
                        <div class="form-group row">
                            <div class="offset-sm-4 offset-md-3 offset-lg-4 offset-xl-3 col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                <button type="submit" class="btn btn-block btn-primary"><i class="fas fa-save"></i>&nbsp;Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/block}
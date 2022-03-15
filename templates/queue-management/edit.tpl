{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Request Queue Management <small class="text-muted">Create and edit request queues</small></h1>
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
                            <legend>Queue details</legend>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="header" class="col-form-label">Header text:</label>
                                </div>
                                <div class="col-sm-7 col-md-6 col-lg-7 col-xl-6">
                                    <input type="text" class="form-control" id="header" name="header" maxlength="100" required="required" placeholder="Checkuser needed" value="{$header|escape}"/>
                                    <small class="form-text text-muted" id="headerHelp">The text in the header of the queue.</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="displayName" class="col-form-label">Target name:</label>
                                </div>
                                <div class="col-sm-5 col-md-4 col-lg-6 col-xl-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">Defer to </div>
                                        </div>
                                        <input type="text" class="form-control" id="displayName" name="displayName" maxlength="100" required="required" placeholder="checkusers" value="{$displayName|escape}"/>
                                    </div>

                                    <small class="form-text text-muted" id="displayNameHelp">The name to use when treating this queue as a target.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="apiName" class="col-form-label">API name:</label>
                                </div>
                                <div class="col-sm-5 col-md-4 col-lg-5 col-xl-4">
                                    <input type="text" class="form-control" id="apiName" name="apiName" maxlength="20" required="required" placeholder="checkuser" pattern="^[A-Za-z][a-zA-Z0-9_-]*$" value="{$apiName|escape}" {if !$createMode}disabled{/if}/>
                                    <small class="form-text text-muted" id="apiNameHelp">The key to use for this queue in the API. Cannot be changed after creation. Must start with a letter, and only contain letters, numbers, hyphens and underscores.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-4 offset-md-3 offset-lg-4 offset-xl-3 col-md-9">
                                    <div class="custom-control custom-switch">
                                        <input class="custom-control-input" type="checkbox" id="enabled" name="enabled" {if $enabled}checked{/if} {if $default || $antispoof || $titleblacklist || $isTarget || $isFormTarget }disabled{/if} />
                                        <label class="custom-control-label" for="enabled">Enabled</label>
                                        <small class="form-text text-muted" id="defaultHelp">Allow new requests to enter this queue. Disabled queues still show in the interface if they contain requests.</small>
                                        {if $default}<small class="form-text text-danger">To unset this, please first mark another queue as the default queue.</small>{/if}
                                        {if $antispoof}<small class="form-text text-danger">To unset this, please first mark another queue as the default AntiSpoof queue.</small>{/if}
                                        {if $titleblacklist}<small class="form-text text-danger">To unset this, please first mark another queue as the default TitleBlacklist queue.</small>{/if}
                                        {if $isTarget}<small class="form-text text-danger">To unset this, please first remove this queue as the target of an active email template. This can be done by deactivating the email template or by moving the email template to a new queue.</small>{/if}
                                        {if $isFormTarget}<small class="form-text text-danger">To unset this, please first remove this queue as the target of an active request form. This can be done by deactivating the request form or by modifying the form to use a different queue.</small>{/if}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-4 offset-md-3 offset-lg-4 offset-xl-3 col-md-9">
                                    <div class="custom-control custom-switch">
                                        <input class="custom-control-input" type="checkbox" id="default" name="default" {if $default}checked disabled{/if} />
                                        <label class="custom-control-label" for="default">Default queue</label>
                                        <small class="form-text text-muted" id="defaultHelp">Mark this queue as the default queue for all requests to arrive into.</small>
                                        {if $default}<small class="form-text text-danger">To unset this, please mark another queue as the default queue.</small>{/if}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-4 offset-md-3 offset-lg-4 offset-xl-3 col-md-9">
                                    <div class="custom-control custom-switch">
                                        <input class="custom-control-input" type="checkbox" id="antispoof" name="antispoof" {if $antispoof}checked disabled{/if} />
                                        <label class="custom-control-label" for="antispoof">Default AntiSpoof queue</label>
                                        <small class="form-text text-muted" id="antispoofHelp">Mark this queue as the default queue for requests with an AntiSpoof hit.</small>
                                        {if $antispoof}<small class="form-text text-danger">To unset this, please mark another queue as the default AntiSpoof queue.</small>{/if}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-4 offset-md-3 offset-lg-4 offset-xl-3 col-md-9">
                                    <div class="custom-control custom-switch">
                                        <input class="custom-control-input" type="checkbox" id="titleblacklist" name="titleblacklist" {if $titleblacklist}checked disabled{/if} />
                                        <label class="custom-control-label" for="titleblacklist">Default Title Blacklist queue</label>
                                        <small class="form-text text-muted" id="titleblacklistHelp">Mark this queue as the default queue for requests with a Title Blacklist hit.</small>
                                        {if $titleblacklist}<small class="form-text text-danger">To unset this, please mark another queue as the default Title Blacklist queue.</small>{/if}
                                    </div>
                                </div>
                            </div>

                        </fieldset>
                    </div>
                    <div class="col-lg-6 col-xs-12">
                        <fieldset>
                            <legend>Help text</legend>
                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="help" class="col-form-label">Help text:</label>
                                </div>
                                <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                    <textarea class="form-control" id="help" rows="5" name="help">{$help|escape}</textarea>
                                    <small class="form-text text-muted" id="helpHelp">Help text for this queue. While multiple lines are supported, please keep it short!</small>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="text-muted">
                            <legend>Legacy config</legend>
                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="logName" class="col-form-label">Log name:</label>
                                </div>
                                <div class="col-sm-5 col-md-4 col-lg-5 col-xl-4">
                                    <input type="text" class="form-control" id="logName" name="logName" maxlength="50" required="required" placeholder="checkuser" value="{$logName|escape}" {if !$createMode}disabled{/if}/>
                                    <small class="form-text text-muted" id="helpHelp">Database key for log messages. Should be lowercase, preferably a single word</small>
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
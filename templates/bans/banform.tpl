{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Ban Management <small class="text-muted">View, ban, and unban requesters</small></h1>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h3>Ban an IP, name, or email address</h3>

            <form method="post">
                {include file="security/csrf.tpl"}

                {if $replaceBanId !== null}
                    <input type="hidden" name="replaceBanId" value="{$replaceBanId}">
                    <input type="hidden" name="replaceBanUpdateVersion" value="{$replaceBanUpdateVersion}">

                    <div class="alert alert-warning">
                        <h4>Replacing ban {$replaceBanId}</h4>
                        By submitting this form, you will unban ban {$replaceBanId} and replace it with a new ban as
                        configured below. The form below is pre-filled with the old ban's details.
                    </div>
                {/if}

                <div class="row">
                    <div class="col-lg-6 col-xs-12">
                        <fieldset>
                            <legend>Ban target</legend>

                            <div class="alert alert-info">
                                All of these fields must match the incoming request in order for a ban to take effect.
                                If you do not wish to match against a field, leave it blank.
                                If you wish to ban with an "or" condition (eg: IP or email), set two bans.
                            </div>

                            {if $canSeeNameBan}
                                <div class="form-group row">
                                    <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                        <label for="banName" class="col-form-label">Username:</label>
                                    </div>
                                    <div class="col-sm-8 col-md-5 col-lg-8 col-xl-9">
                                        <input type="text" class="form-control" id="banName" name="banName" {if $banName != ""}value="{$banName|escape}"{/if} />
                                        <small class="text-muted">A regular expression matching the username to ban</small>
                                    </div>
                                </div>
                            {/if}

                            {if $canSeeIpBan}
                                <div class="form-group row">
                                    <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                        <label for="banIP" class="col-form-label">IP address:</label>
                                    </div>
                                    <div class="col-sm-8 col-md-5 col-lg-8 col-xl-6">
                                        <input type="text" class="form-control" id="banIP" name="banIP" {if $banIP != ""}value="{$banIP|escape}"{/if} />
                                        <small class="text-muted">The IP address or CIDR range to ban</small>
                                        <small class="text-muted d-block">The maximum range allowed when using a block or drop action is a /{$maxIpBlockRange[4]} for IPv4, and a /{$maxIpBlockRange[6]} for IPv6. The maximum range allowed generally is a /{$maxIpRange[4]} for IPv4, and a /{$maxIpRange[6]} for IPv6.</small>
                                    </div>
                                </div>
                            {/if}

                            {if $canSeeEmailBan}
                                <div class="form-group row">
                                    <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                        <label for="banEmail" class="col-form-label">Email address:</label>
                                    </div>
                                    <div class="col-sm-8 col-md-5 col-lg-8 col-xl-9">
                                        <input type="text" class="form-control" id="banEmail" name="banEmail" {if $banEmail != ""}value="{$banEmail|escape}"{/if} />
                                        <small class="text-muted">A regular expression matching the email address to ban</small>
                                    </div>
                                </div>
                            {/if}

                            {if $canSeeUseragentBan}
                                <div class="form-group row">
                                    <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                        <label for="banUseragent" class="col-form-label">User agent:</label>
                                    </div>
                                    <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                        <input type="text" class="form-control" id="banUseragent" name="banUseragent" {if $banUseragent != ""}value="{$banUseragent|escape}"{/if} />
                                        <small class="text-muted">A regular expression matching the user agent to ban</small>
                                    </div>
                                </div>
                            {/if}
                        </fieldset>
                    </div>
                    <div class="col-lg-6 col-xs-12">
                        <fieldset>
                            <legend>Ban settings</legend>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="banReason" class="col-form-label">Reason:</label>
                                </div>
                                <div class="col-sm-8 col-md-9 col-lg-8 col-xl-9">
                                    <input type="text" class="form-control" id="banReason" name="banreason" required="required" {if $banReason != ""}value="{$banReason|escape}"{/if}/>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="banDuration" class="col-form-label">Duration:</label>
                                </div>
                                <div class="col-sm-8 col-md-5 col-lg-8 col-xl-6">
                                    <select class="form-control" name="duration" required="required" id="banDuration">
                                        <option value="-1">Indefinite</option>
                                        <option value="86400">24 Hours</option>
                                        <option value="604800">One Week</option>
                                        <option value="2629743">One Month</option>
                                        <option value="other" {if $banDuration != ""}selected{/if}>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row {if $banDuration == ""}d-none{/if}" id="banCustomDurationSelection">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="otherDuration" class="col-form-label">Other duration:</label>
                                </div>
                                <div class="col-sm-8 col-md-5 col-lg-8 col-xl-4">
                                    <input class="form-control" type="text" id="otherDuration" name="otherduration" {if $banDuration != ""}value="{$banDuration|escape}"{/if}/>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="banAction" class="col-form-label">Action:</label>
                                </div>
                                <div class="col-sm-8 col-md-5 col-lg-8 col-xl-6">
                                    <select class="form-control" name="banAction" required="required" id="banAction">
                                        <option value="{Waca\DataObjects\Ban::ACTION_BLOCK}" {if $banAction === Waca\DataObjects\Ban::ACTION_BLOCK}selected{/if}>
                                            Block submission of the request
                                        </option>
                                        <option value="{Waca\DataObjects\Ban::ACTION_DROP}" {if $banAction === Waca\DataObjects\Ban::ACTION_DROP}selected{/if}>
                                            Drop the request silently
                                        </option>
                                        <option value="{Waca\DataObjects\Ban::ACTION_DEFER}" {if $banAction === Waca\DataObjects\Ban::ACTION_DEFER}selected{/if}>
                                            Defer the request to the specified queue
                                        </option>
                                        <option value="{Waca\DataObjects\Ban::ACTION_NONE}" {if $banAction === Waca\DataObjects\Ban::ACTION_NONE}selected{/if}>
                                            Do nothing - report only
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row {if $banAction !== Waca\DataObjects\Ban::ACTION_DEFER}d-none{/if}" id="banDeferTargetSelection">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="banActionTarget" class="col-form-label">Defer to:</label>
                                </div>
                                <div class="col-sm-8 col-md-5 col-lg-8 col-xl-6">
                                    <select class="form-control" name="banActionTarget" required="required" id="banActionTarget">
                                        {foreach $requestQueues as $queue}
                                            <option value="{$queue->getApiName()|escape}" {if $banQueue === $queue->getId()}selected{/if}>{$queue->getHeader()|escape}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label for="banVisibility" class="col-form-label">Restrict reason visibility:</label>
                                </div>
                                <div class="col-sm-8 col-md-5 col-lg-8 col-xl-6">
                                    <select class="form-control" name="banVisibility" required="required" id="banVisibility">
                                        {if $canSeeUserVisibility}
                                            <option value="user" {if $banVisibility == 'user'}selected{/if}>All users</option>
                                        {/if}
                                        {if $canSeeAdminVisibility}
                                            <option value="admin" {if $banVisibility == 'admin'}selected{/if}>Tool admins and CheckUsers</option>
                                        {/if}
                                        {if $canSeeCheckuserVisibility}
                                            <option value="checkuser" {if $banVisibility == 'checkuser'}selected{/if}>CheckUsers</option>
                                        {/if}
                                    </select>
                                </div>
                            </div>
                            {if $canGlobalBan}
                                <div class="form-group row">
                                    <div class="offset-sm-4 col-sm-8 offset-md-3 col-md-5 offset-lg-4 col-lg-8 offset-xl-3 col-xl-6">
                                        <div class="custom-control custom-switch">
                                            <input class="custom-control-input" type="checkbox" id="banGlobal" name="banGlobal"
                                                   {if $banGlobal}checked{/if}
                                                   {if $banAction === Waca\DataObjects\Ban::ACTION_DEFER}disabled{/if}>
                                            <label class="custom-control-label" for="banGlobal">Apply ban across all domains</label>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </fieldset>
                    </div>
                </div>


                <div class="form-group row">
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="offset-sm-4 offset-md-3 offset-lg-4 offset-xl-3 col-sm-8 col-md-5 col-lg-8 col-xl-9">
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fas fa-ban"></i>&nbsp;Ban
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/block}

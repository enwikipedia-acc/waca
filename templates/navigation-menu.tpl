{* README! Variables introduced here must be configured in TemplateOutput, and probably InternalPageBase too. *}
<div class="collapse navbar-collapse">
    <ul class="navbar-nav mr-auto">
        {if $nav__canRequests}
            <li class="nav-item"><a class="nav-link" href="{$baseurl}/internal.php"><i class="fas fa-home"></i>&nbsp;Requests</a></li>
        {/if}
        {if $nav__canLogs || $nav__canUsers || $nav__canSearch || $nav__canStats }
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown"><i class="fas fa-tag"></i>&nbsp;Meta</a>
                <div class="dropdown-menu">
                    {if $nav__canLogs}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/logs"><i class="fas fa-list"></i>&nbsp;Logs</a>
                    {/if}
                    {if $nav__canUsers}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/statistics/users"><i class="fas fa-user"></i>&nbsp;Users</a>
                    {/if}
                    {if $nav__canSearch}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/search"><i class="fas fa-search"></i>&nbsp;Search</a>
                    {/if}
                    {if $nav__canStats}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/statistics"><i class="fas fa-tasks"></i>&nbsp;Statistics</a>
                    {/if}
                </div>
            </li>
        {/if}
        {if $nav__canBan || $nav__canEmailMgmt || $nav__canWelcomeMgmt || $nav__canSiteNoticeMgmt || $nav__canUserMgmt || $nav__canJobQueue || $nav__canFlaggedComments || $nav__canQueueMgmt || $nav__canFormMgmt || $nav__canDomainMgmt || $nav__canErrorLog}
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown">
                    <i class="fas fa-wrench"></i>&nbsp;Admin
                    {if $nav__numAdmin > 0}
                        <div class="badge badge-danger">{$nav__numAdmin}</div>
                    {/if}
                </a>
                <div class="dropdown-menu">
                    {if $nav__canBan}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/bans"><i class="fas fa-ban"></i>&nbsp;Ban Management</a>
                    {/if}
                    {if $nav__canEmailMgmt}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/emailManagement"><i class="fas fa-envelope"></i>&nbsp;Close Email Management</a>
                    {/if}
                    {if $nav__canWelcomeMgmt}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/welcomeTemplates"><i class="fas fa-file"></i>&nbsp;Welcome Template Management</a>
                    {/if}
                    {if $nav__canSiteNoticeMgmt}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/siteNotice"><i class="fas fa-print"></i>&nbsp;Site Notice Management</a>
                    {/if}
                    {if $nav__canUserMgmt}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/userManagement"><i class="fas fa-user"></i> User Management</a>
                    {/if}
                    {if $nav__canJobQueue}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/jobQueue">
                            <i class="fas fa-tools"></i> Job Queue
                            {if $nav__numJobQueueFailed > 0}
                                <span class="badge badge-danger">
                                    {$nav__numJobQueueFailed}
                                </span>
                            {/if}
                        </a>
                    {/if}
                    {if $nav__canFlaggedComments}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/flaggedComments">
                            <i class="fas fa-flag"></i> Flagged Comments
                            {if $nav__numFlaggedComments > 0}
                                <span class="badge badge-danger">
                                    {$nav__numFlaggedComments}
                                </span>
                            {/if}
                        </a>
                    {/if}
                    {if $nav__canQueueMgmt}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/queueManagement"><i class="fas fa-list-ol"></i> Request Queue Management</a>
                    {/if}
                    {if $nav__canFormMgmt}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/requestFormManagement"><i class="fas fa-align-justify"></i> Request Form Management</a>
                    {/if}
                    {if $nav__canDomainMgmt}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/domainManagement"><i class="fas fa-globe-europe"></i> Domain Management</a>
                    {/if}
                    {if $nav__canErrorLog}
                        <a class="dropdown-item" href="{$baseurl}/internal.php/errorLog"><i class="fas fa-bug"></i> Exception Log</a>
                    {/if}
                </div>
            </li>
        {/if}
        {if $nav__canViewRequest}
            <li class="nav-item">
                <form class="navbar-form form-search" action="{$baseurl}/internal.php/viewRequest">
                    <label class="sr-only" for="jumpToReq">Enter request ID to jump to request</label>
                    <input class="form-control text-white bg-dark border-0" type="number" data-toggle="tooltip" id="jumpToReq"
                           data-title="Enter request ID to jump to request" placeholder="Jump to request ID" name="id">
                </form>
            </li>
        {/if}
    </ul>
    <ul class="navbar-nav ml-auto">
        {if ! $currentUser->isCommunityUser()}
            {if count($nav__domainList) > 1}
                <li class="nav-item dropdown pr-lg-3">
                    <a href="#" class="nav-link dropdown-toggle" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown"><i class="fas fa-globe-europe"></i>
                        {$currentDomain->getLongName()}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <form action="{$baseurl}/internal.php/domainSwitch" method="post">
                            <input type="hidden" name="referrer" value="{Waca\WebRequest::pathInfo()|join:'/'|escape}" />
                            {foreach from=$nav__domainList item=domain}
                                <button class="dropdown-item" type="submit" name="newdomain" value="{$domain->getId()|escape}">
                                    <i class="fas fa-globe-europe"></i>&nbsp;<code>{$domain->getShortName()|escape}</code>:&nbsp;{$domain->getLongName()|escape}
                                </button>
                            {/foreach}
                        </form>
                    </div>
                </li>
            {/if}
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown"><i class="fas fa-user"></i>
                    {$currentUser->getUsername()}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    {if count($nav__domainList) == 1}
                        <h6 class="dropdown-header">Current domain</h6>
                        <span class="dropdown-item disabled text-muted">
                            <i class="fas fa-globe-europe"></i>
                            {$currentDomain->getLongName()}
                        </span>
                    {/if}
                    <h6 class="dropdown-header">Account</h6>
                    <a class="dropdown-item" href="{$baseurl}/internal.php/statistics/users/detail?user={$currentUser->getId()}">
                        <i class="fas fa-tasks"></i> My statistics
                    </a>
                    <a class="dropdown-item" href="{$baseurl}/internal.php/preferences">
                        <i class="fas fa-edit"></i> Edit preferences
                    </a>
                    <a class="dropdown-item" href="{$baseurl}/internal.php/changePassword">
                        <i class="fas fa-key"></i> Change password
                    </a>
                    <a class="dropdown-item" href="{$baseurl}/internal.php/multiFactor">
                        <i class="fas fa-qrcode"></i> Configure multi-factor credentials
                    </a>
                    <a class="dropdown-item {if $siteNoticeState !== 'd-none'}d-none{/if} sitenotice-show" href="">
                        <i class="far fa-eye-slash"></i> Un-hide site notice
                    </a>
                    <div class="dropdown-divider"></div>

                    <h6 class="dropdown-header">Help</h6>
                    <a class="dropdown-item" href="{$currentDomain->getLocalDocumentation()|escape}" target="_blank">
                        <i class="fas fa-question-circle"></i>&nbsp;Guide
                    </a>
                    <a class="dropdown-item" href="https://en.wikipedia.org/wiki/Wikipedia:Username_policy" target="_blank">
                        <i class="fas fa-exclamation-triangle"></i>&nbsp;Username policy
                    </a>
                    <!--suppress HtmlUnknownAnchorTarget -->
                    <a class="dropdown-item" href="#modalFlowchart" role="button" data-toggle="modal">
                        <i class="fas fa-check"></i>&nbsp;Similar account flowchart
                    </a>
                    <a class="dropdown-item" href="https://kiwiirc.com/nextclient/irc.libera.chat/wikipedia-en-accounts" target="_blank">
                        <i class="fas fa-comment"></i>&nbsp;Chat
                    </a>
                    <a class="dropdown-item" href="https://lists.wikimedia.org/mailman/listinfo/accounts-enwiki-l" target="_blank">
                        <i class="fas fa-envelope"></i>&nbsp;Mailing list
                    </a>
                    <div class="dropdown-divider"></div>

                    <form action="{$baseurl}/internal.php/logout" method="post">
                        <button class="dropdown-item" type="submit">
                            <i class="fas fa-power-off"></i>&nbsp;Logout
                        </button>
                    </form>
                </div>
            </li>
        {else}
            <li>
                <span class="navbar-text text-muted pull-right">
                    <a class="text-muted" href="{$baseurl}/internal.php/login"><strong>Not logged in</strong></a>
                </span>
            </li>
        {/if}
    </ul>
</div>

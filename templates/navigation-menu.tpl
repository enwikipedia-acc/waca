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
        {if $nav__canBan || $nav__canEmailMgmt || $nav__canWelcomeMgmt || $nav__canSiteNoticeMgmt || $nav__canUserMgmt || $nav__canJobQueue}
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown"><i class="fas fa-wrench"></i>&nbsp;Admin</a>
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
                        <a class="dropdown-item" href="{$baseurl}/internal.php/jobQueue"><i class="fas fa-tools"></i> Job Queue</a>
                    {/if}
                </div>
            </li>
        {/if}
        {if $nav__canViewRequest}
            <li class="nav-item">
                <form class="navbar-form form-search" action="{$baseurl}/internal.php/viewRequest">
                    <input class="form-control text-white bg-dark border-0" type="text" placeholder="Jump to request ID" name="id" class="search-query">
                </form>
            </li>
        {/if}
    </ul>
    <ul class="navbar-nav ml-auto">
        {if ! $currentUser->isCommunityUser()}
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" ria-haspopup="true" aria-expanded="false" data-toggle="dropdown"><i class="fas fa-user"></i>
                    {$currentUser->getUsername()}
                </a>
                <div class="dropdown-menu dropdown-menu-right">

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
                    <div class="dropdown-divider"></div>

                    <h6 class="dropdown-header">Help</h6>
                    <a class="dropdown-item" href="//en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide">
                        <i class="fas fa-question-circle"></i>&nbsp;Guide
                    </a>
                    <a class="dropdown-item" href="//en.wikipedia.org/wiki/Wikipedia:Username_policy">
                        <i class="fas fa-exclamation-triangle"></i>&nbsp;Username policy
                    </a>
                    <a class="dropdown-item" href="#modalFlowchart" role="button" data-toggle="modal">
                        <i class="fas fa-check"></i>&nbsp;Similar account flowchart
                    </a>
                    <a class="dropdown-item" href="https://webchat.freenode.net/?channels=wikipedia-en-accounts">
                        <i class="fas fa-comment"></i>&nbsp;Chat
                    </a>
                    <a class="dropdown-item" href="https://lists.wikimedia.org/mailman/listinfo/accounts-enwiki-l">
                        <i class="fas fa-envelope"></i>&nbsp;Mailing list
                    </a>
                    <div class="dropdown-divider"></div>

                    <a class="dropdown-item" href="{$baseurl}/internal.php/logout">
                        <i class="fas fa-power-off"></i>&nbsp;Logout
                    </a>
                </div>
            </li>
        {else}
            <li>
                <span class="navbar-text text-muted pull-right">
                    <strong>Not logged in</strong>
                </span>
            </li>
        {/if}
    </ul>
</div>

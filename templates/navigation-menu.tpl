{* README! Variables introduced here must be configured in TemplateOutput, and probably InternalPageBase too. *}
<div class="collapse navbar-collapse">
    <ul class="navbar-nav">
        {if $nav__canRequests}
            <li><a class="nav-link" href="{$baseurl}/internal.php"><i class="fas fa-home "></i>&nbsp;Requests</a></li>
        {/if}
        {if $nav__canLogs || $nav__canUsers || $nav__canSearch || $nav__canStats }
            <li class="nav-link dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i
                        class="fas fa-tag"></i>&nbsp;Meta&nbsp;</a>&nbsp;
                <ul class="dropdown-menu bg-dark text-white">
                    {if $nav__canLogs}
                        <li class="dropdown-item"><a href="{$baseurl}/internal.php/logs"><i class="fas fa-list"></i>&nbsp;Logs</a></li>
                    {/if}
                    {if $nav__canUsers}
                        <li class="dropdown-item"><a href="{$baseurl}/internal.php/statistics/users"><i class="fas fa-user"></i>&nbsp;Users</a></li>
                    {/if}
                    {if $nav__canSearch}
                        <li class="dropdown-item"><a href="{$baseurl}/internal.php/search"><i class="fas fa-search"></i>&nbsp;Search</a></li>
                    {/if}
                    {if $nav__canStats}
                        <li class="dropdown-item"><a href="{$baseurl}/internal.php/statistics"><i class="fas fa-tasks"></i>&nbsp;Statistics</a></li>
                    {/if}
                </ul>
            </li>
        {/if}
        {if $nav__canBan || $nav__canEmailMgmt || $nav__canWelcomeMgmt || $nav__canSiteNoticeMgmt || $nav__canUserMgmt}
            <li class="nav-link dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fas fa-wrench"></i>&nbsp;Admin&nbsp;</a>
                <ul class="dropdown-menu bg-dark">
                    {if $nav__canBan}
                        <li class="dropdown-item"><a href="{$baseurl}/internal.php/bans"><i class="fas fa-ban"></i>&nbsp;Ban Management</a></li>
                    {/if}
                    {if $nav__canEmailMgmt}
                    <li class="dropdown-item"><a href="{$baseurl}/internal.php/emailManagement"><i class="fas fa-envelope"></i>&nbsp;Close Email
                            Management</a></li>
                    {/if}
                    {if $nav__canWelcomeMgmt}
                    <li class="dropdown-item"><a href="{$baseurl}/internal.php/welcomeTemplates"><i class="fas fa-file"></i>&nbsp;Welcome
                            Template Management</a></li>
                    {/if}
                    {if $nav__canSiteNoticeMgmt}
                    <li class="dropdown-item"><a href="{$baseurl}/internal.php/siteNotice"><i class="fas fa-print"></i>&nbsp;Site Notice
                            Management</a></li>
                    {/if}
                    {if $nav__canUserMgmt}
                    <li class="dropdown-item"><a href="{$baseurl}/internal.php/userManagement"><i class="fas fa-user"></i> User
                            Management</a></li>
                    {/if}
                </ul>
            </li>
        {/if}
        {if $nav__canViewRequest}
            <li class="nav-item">
                <form class="navbar-form form-search" action="{$baseurl}/internal.php/viewRequest">
                    <input class="span2" type="text" placeholder="Request ID" name="id" class="search-query">
                </form>
            </li>
        {/if}
    </ul>
    <ul class="navbar-nav ml-auto">
        {if ! $currentUser->isCommunityUser()}
            <li class="nav-link dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fas fa-user "></i>
                    <strong>{$currentUser->getUsername()}</strong> <b class="caret"></b></a>
                <ul class="dropdown-menu dropdown-menu-right bg-dark">
                    <li class="dropdown-header">Account</li>
                    <li class="dropdown-item">
                        <a href="{$baseurl}/internal.php/statistics/users/detail?user={$currentUser->getId()}">
                            <i class="fas fa-tasks"></i> My statistics
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="{$baseurl}/internal.php/preferences">
                            <i class="fas fa-edit"></i> Edit preferences
                        </a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li class="dropdown-header">Help</li>
                    <li class="dropdown-item">
                        <a href="//en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide">
                            <i class="fas fa-question-circle"></i>&nbsp;Guide
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="//en.wikipedia.org/wiki/Wikipedia:Username_policy">
                            <i class="fas fa-exclamation-triangle"></i>&nbsp;Username policy
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="#modalFlowchart" role="button" data-toggle="modal">
                            <i class="fas fa-check"></i>&nbsp;Similar account flowchart
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="https://webchat.freenode.net/?channels=wikipedia-en-accounts">
                            <i class="fas fa-comment"></i>&nbsp;Chat
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="https://lists.wikimedia.org/mailman/listinfo/accounts-enwiki-l">
                            <i class="fas fa-envelope"></i>&nbsp;Mailing list
                        </a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li class="dropdown-item"><a href="{$baseurl}/internal.php/logout"><i class="fas fa-lock"></i> Logout</a></li>

                </ul>
            </li>
        {else}
            <li>
                <p class="navbar-text pull-right">
                    <strong>Not logged in</strong>
                </p>
            </li>
        {/if}
    </ul>
</div>

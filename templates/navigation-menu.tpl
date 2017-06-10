{* README! Variables introduced here must be configured in TemplateOutput, and probably InternalPageBase too. *}
<div class="nav-collapse collapse">
    <ul class="nav">
        {if $nav__canRequests}
            <li><a href="{$baseurl}/internal.php"><i class="icon-home icon-white"></i>&nbsp;Requests</a></li>
        {/if}
        {if $nav__canLogs || $nav__canUsers || $nav__canSearch || $nav__canStats }
            <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i
                        class="icon-tag icon-white"></i>&nbsp;Meta&nbsp;<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {if $nav__canLogs}
                        <li><a href="{$baseurl}/internal.php/logs"><i class="icon-list"></i>&nbsp;Logs</a></li>
                    {/if}
                    {if $nav__canUsers}
                        <li><a href="{$baseurl}/internal.php/statistics/users"><i class="icon-user"></i>&nbsp;Users</a></li>
                    {/if}
                    {if $nav__canSearch}
                        <li><a href="{$baseurl}/internal.php/search"><i class="icon-search"></i>&nbsp;Search</a></li>
                    {/if}
                    {if $nav__canStats}
                        <li><a href="{$baseurl}/internal.php/statistics"><i class="icon-tasks"></i>&nbsp;Statistics</a></li>
                    {/if}
                </ul>
            </li>
        {/if}
        {if $nav__canBan || $nav__canEmailMgmt || $nav__canWelcomeMgmt || $nav__canSiteNoticeMgmt || $nav__canUserMgmt}
            <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i
                            class="icon-wrench icon-white"></i>&nbsp;Admin&nbsp;<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {if $nav__canBan}
                        <li><a href="{$baseurl}/internal.php/bans"><i class="icon-ban-circle"></i>&nbsp;Ban Management</a></li>
                    {/if}
                    {if $nav__canEmailMgmt}
                    <li><a href="{$baseurl}/internal.php/emailManagement"><i class="icon-envelope"></i>&nbsp;Close Email
                            Management</a></li>
                    {/if}
                    {if $nav__canWelcomeMgmt}
                    <li><a href="{$baseurl}/internal.php/welcomeTemplates"><i class="icon-file"></i>&nbsp;Welcome
                            Template Management</a></li>
                    {/if}
                    {if $nav__canSiteNoticeMgmt}
                    <li><a href="{$baseurl}/internal.php/siteNotice"><i class="icon-print"></i>&nbsp;Site Notice
                            Management</a></li>
                    {/if}
                    {if $nav__canUserMgmt}
                    <li><a href="{$baseurl}/internal.php/userManagement"><i class="icon-user"></i> User
                            Management</a></li>
                    {/if}
                </ul>
            </li>
        {/if}
        {if $nav__canViewRequest}
            <li>
                <form class="navbar-form form-search" action="{$baseurl}/internal.php/viewRequest">
                    <input class="span2" type="text" placeholder="Request ID" name="id" class="search-query">
                </form>
            </li>
        {/if}
    </ul>
    <ul class="nav pull-right">
        {if ! $currentUser->isCommunityUser()}
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i>
                    <strong>{$currentUser->getUsername()}</strong> <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li class="nav-header">Account</li>
                    <li>
                        <a href="{$baseurl}/internal.php/statistics/users/detail?user={$currentUser->getId()}">
                            <i class="icon-tasks"></i> My statistics
                        </a>
                    </li>
                    <li>
                        <a href="{$baseurl}/internal.php/preferences">
                            <i class="icon-edit"></i> Edit preferences
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li class="nav-header">Help</li>
                    <li>
                        <a href="//en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide">
                            <i class="icon-question-sign"></i>&nbsp;Guide
                        </a>
                    </li>
                    <li>
                        <a href="//en.wikipedia.org/wiki/Wikipedia:Username_policy">
                            <i class="icon-warning-sign"></i>&nbsp;Username policy
                        </a>
                    </li>
                    <li>
                        <a href="#modalFlowchart" role="button" data-toggle="modal">
                            <i class="icon-check"></i>&nbsp;Similar account flowchart
                        </a>
                    </li>
                    <li>
                        <a href="https://webchat.freenode.net/?channels=wikipedia-en-accounts">
                            <i class="icon-comment"></i>&nbsp;Chat
                        </a>
                    </li>
                    <li>
                        <a href="https://lists.wikimedia.org/mailman/listinfo/accounts-enwiki-l">
                            <i class="icon-envelope"></i>&nbsp;Mailing list
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li><a href="{$baseurl}/internal.php/logout"><i class="icon-lock"></i> Logout</a></li>

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

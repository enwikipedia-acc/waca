{* This is the base template for all internal-area pages. *}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{if isset($htmlTitle) && $htmlTitle !== null}{$htmlTitle} :: {/if}Account Creation Interface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- bootstrap styles -->
    <link href="{$baseurl}/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{$baseurl}/resources/baseStyles.css" rel="stylesheet" />
    <link href="{$baseurl}/lib/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
    <link href="{$baseurl}/lib/bootstrap-sortable/css/bootstrap-sortable.css" rel="stylesheet" />

    <!-- Our extra styles -->
    <link href="{$baseurl}/resources/styles.css" rel="stylesheet" />
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="brand" href="{$baseurl}/internal.php">Account Creation Interface</a>
            {block name="navmenu"}<div class="nav-collapse collapse">
                <ul class="nav">
                    {if ! $currentUser->isCommunityUser()}
                        <li><a href="{$baseurl}/internal.php"><i class="icon-home icon-white"></i>&nbsp;Requests</a></li>
                        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-tag icon-white"></i>&nbsp;Meta&nbsp;<b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="{$baseurl}/internal.php/logs"><i class="icon-list"></i>&nbsp;Logs</a></li>
                                <li><a href="{$baseurl}/internal.php/statistics/users"><i class="icon-user"></i>&nbsp;Users</a></li>
                                <li><a href="{$baseurl}/internal.php/search"><i class="icon-search"></i>&nbsp;Search</a></li>
                                <li><a href="{$baseurl}/internal.php/statistics"><i class="icon-tasks"></i>&nbsp;Statistics</a></li>
                            </ul>
                        </li>
                        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-wrench icon-white"></i>&nbsp;Admin&nbsp;<b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="{$baseurl}/internal.php/bans"><i class="icon-ban-circle"></i>&nbsp;Ban Management</a></li>
                                <li><a href="{$baseurl}/internal.php/emailmgmt"><i class="icon-envelope"></i>&nbsp;Close Email Management</a></li>
                                <li><a href="{$baseurl}/internal.php/welcomeTemplates"><i class="icon-file"></i>&nbsp;Welcome Template Management</a></li>
                                {if $currentUser->isAdmin()}
                                    <li><a href="{$baseurl}/internal.php/siteNotice"><i class="icon-print"></i>&nbsp;Site Notice Management</a></li>
                                    <li><a href="{$baseurl}/internal.php/userManagement"><i class="icon-user"></i> User Management</a></li>
                                {/if}
                            </ul>
                        </li>
                        <li>
                            <form class="navbar-form form-search" action="{$baseurl}/acc.php">
                                <input type="hidden" name="action" value="zoom">
                                <input class="span2" type="text" placeholder="Request ID" name="id" class="search-query">
                            </form>
                        </li>
                    {/if}
                </ul>
                <ul class="nav pull-right">
                    {if ! $currentUser->isCommunityUser()}
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> <strong>{$currentUser->getUsername()}</strong> <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li class="nav-header">Account</li>
                                <li><a href="{$baseurl}/internal.php/statistics/users/detail?user={$currentUser->getId()}"><i class="icon-tasks"></i> My statistics</a></li>
                                <li><a href="{$baseurl}/internal.php/preferences"><i class="icon-edit"></i> Edit Preferences</a></li>
                                <li class="divider"></li>
                                <li class="nav-header">Help</li>
                                <li><a href="//en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide"><i class="icon-question-sign"></i>&nbsp;Guide</a></li>
                                <li><a href="//en.wikipedia.org/wiki/Wikipedia:Username_policy"><i class="icon-warning-sign"></i>&nbsp;Username Policy</a></li>
                                <li><a href="#modalFlowchart" role="button" data-toggle="modal"><i class="icon-check"></i>&nbsp;Similar account flowchart</a></li>
                                <li><a href="http://webchat.freenode.net/?channels=wikipedia-en-accounts"><i class="icon-comment"></i>&nbsp;Chat</a></li>
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
                </div><!--/.nav-collapse -->{/block}
        </div>
    </div>
</div>

{block name="modals"}{include file="modal-flowchart.tpl"}{/block}

<div class="container-fluid">
    {block name="sitenotice"}{/block}

    {block name="content"}This page doesn't do anything. If you see this, and you're not a developer, this is a bug.{/block}

    <hr />

    <footer class="row-fluid">
        <p class="{if $onlineusers == ""}span12{else}span6{/if}"><small>Account Creation Assistance Manager (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>) by <a href="{$baseurl}/team.php">The ACC development team</a> (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>).</small></p>
        {$onlineusers}
    </footer>

</div><!--/.fluid-container-->

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="{$baseurl}/vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
<script src="{$baseurl}/lib/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="{$baseurl}/lib/bootstrap-sortable/js/bootstrap-sortable.js" type="text/javascript"></script>

{* initialise the tooltips *}
<script type="text/javascript">
    $(function () {
        $("[rel='tooltip']").tooltip();
    });
</script>
<script type="text/javascript">
    $(function () {
        $("[rel='popover']").popover();
    });
</script>
{if $tailscript}
    <script type="text/javascript">
        {$tailscript}
    </script>
{/if}
</body>
</html>


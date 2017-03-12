<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Account Creation Interface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="{$baseurl}/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }

      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
    </style>
    <link href="{$baseurl}/lib/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="{$baseurl}/lib/bootstrap-sortable/css/bootstrap-sortable.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="{$baseurl}/lib/bootstrap/js/html5shiv.js"></script>
    <![endif]-->

    <!--  Temporary fix to deal with https://github.com/twbs/bootstrap/issues/7968
	until a newer Bootstrap version with this fixed is released and we upgrade to it -->
	<style>
	.dropdown-backdrop {
		position: static;
	}
	</style>

	<!-- Our extra styles -->
    <link href="{$baseurl}/extra-styles.css" rel="stylesheet">
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
          <a class="brand" href="{$baseurl}/acc.php">Account Creation Interface</a>
          {block name="navmenu"}<div class="nav-collapse collapse">
            <ul class="nav">
			{if $userid != 0}
              <li{* class="active"*}><a href="{$baseurl}/acc.php"><i class="icon-home icon-white"></i>&nbsp;Requests</a></li>
			  <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-tag icon-white"></i>&nbsp;Meta&nbsp;<b class="caret"></b></a>
				  <ul class="dropdown-menu">
					<li><a href="{$baseurl}/acc.php?action=logs"><i class="icon-list"></i>&nbsp;Logs</a></li>
					<li><a href="{$baseurl}/statistics.php?page=Users"><i class="icon-user"></i>&nbsp;Users</a></li>
					<li><a href="{$baseurl}/search.php"><i class="icon-search"></i>&nbsp;Search</a></li>
					<li><a href="{$baseurl}/statistics.php"><i class="icon-tasks"></i>&nbsp;Statistics</a></li>
				  </ul>
			  </li>
              <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-wrench icon-white"></i>&nbsp;Admin&nbsp;<b class="caret"></b></a>
				  <ul class="dropdown-menu">
					<li><a href="{$baseurl}/acc.php?action=ban"><i class="icon-ban-circle"></i>&nbsp;Ban Management</a></li>
					<li><a href="{$baseurl}/acc.php?action=messagemgmt"><i class="icon-print"></i>&nbsp;Message Management</a></li>
					<li><a href="{$baseurl}/acc.php?action=emailmgmt"><i class="icon-envelope"></i>&nbsp;Close Email Management</a></li>
					<li><a href="{$baseurl}/acc.php?action=templatemgmt"><i class="icon-file"></i>&nbsp;Welcome Template Management</a></li>
					{if $currentUser->isAdmin()}
					<li><a href="{$baseurl}/users.php"><i class="icon-user"></i> User Management</a></li>
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
			{if $userid != 0}
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> <strong>{$username}</strong> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li class="nav-header">Account</li>
						<li><a href="{$baseurl}/statistics.php?page=Users&amp;user={$userid}"><i class="icon-tasks"></i> My statistics</a></li>
						<li><a href="{$baseurl}/acc.php?action=prefs"><i class="icon-edit"></i> Edit preferences</a></li>
						<li class="divider"></li>
						<li class="nav-header">Help</li>
						<li><a href="//en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide"><i class="icon-question-sign"></i>&nbsp;Guide</a></li>
						<li><a href="//en.wikipedia.org/wiki/Wikipedia:Username_policy"><i class="icon-warning-sign"></i>&nbsp;Username policy</a></li>
						<li><a href="#modalFlowchart" role="button" data-toggle="modal"><i class="icon-check"></i>&nbsp;Similar account flowchart</a></li>
						<li><a href="https://webchat.freenode.net/?channels=wikipedia-en-accounts"><i class="icon-comment"></i>&nbsp;Chat</a></li>
						<li><a href="https://lists.wikimedia.org/mailman/listinfo/accounts-enwiki-l"><i class="icon-envelope"></i>&nbsp;Mailing list</a></li>
						<li class="divider"></li>
						<li><a href="{$baseurl}/acc.php?action=logout"><i class="icon-lock"></i> Logout</a></li>
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
	{block name="sitenotice"}
	{if $userid != 0}
		<div class="row-fluid">
			<!-- site notice -->
			<div class="span12">
			<div class="alert alert-block">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				{$sitenotice}
			</div>
			</div>
		</div><!--/row-->
	{/if}
	{/block}
	{if count($alerts) > 0}
		{foreach $alerts as $a}
			{$a->getAlertBox()}
		{/foreach}
	{/if}

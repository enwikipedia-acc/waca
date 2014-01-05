<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>English Wikipedia Internal Account Creation Interface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="{$tsurl}/lib/bootstrap/css/bootstrap.css" rel="stylesheet">
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
    <link href="{$tsurl}/lib/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="{$tsurl}/lib/bootstrap-sortable/css/bootstrap-sortable.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="{$tsurl}/lib/bootstrap/js/html5shiv.js"></script>
    <![endif]-->
    
    <!--  Temporary fix to deal with https://github.com/twbs/bootstrap/issues/7968
	until a newer Bootstrap version with this fixed is released and we upgrade to it -->
	<style>
	.dropdown-backdrop {
		position: static;
	}
	</style>
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
          <a class="brand" href="{$tsurl}/acc.php">Account Creation Interface</a>
          {block name="navmenu"}<div class="nav-collapse collapse">
            <ul class="nav">
              <li{* class="active"*}><a href="{$tsurl}/acc.php"><i class="icon-home icon-white"></i> Requests</a></li>
			  <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Meta<b class="caret"></b></a>
				  <ul class="dropdown-menu">
					<li><a href="{$tsurl}/acc.php?action=logs"><i class="icon-list"></i> Logs</a></li>
					<li><a href="{$tsurl}/statistics.php?page=Users"><i class="icon-user"></i> Users</a></li>
					<li><a href="{$tsurl}/search.php"><i class="icon-search"></i> Search</a></li>
					<li><a href="{$tsurl}/statistics.php"><i class="icon-tasks"></i> Statistics</a></li>
				  </ul>
			  </li>
              <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Management<b class="caret"></b></a>
				  <ul class="dropdown-menu">
					<li><a href="{$tsurl}/acc.php?action=ban"><i class="icon-ban-circle"></i> Ban Management</a></li>
					<li><a href="{$tsurl}/acc.php?action=messagemgmt"><i class="icon-print"></i> Message Management</a></li>
					<li><a href="{$tsurl}/acc.php?action=templatemgmt"><i class="icon-file"></i> Template Management</a></li>
					<li><a href="{$tsurl}/users.php"><i class="icon-user"></i> User Management</a></li>
				  </ul>
			  </li>
              <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Help<b class="caret"></b></a>
				  <ul class="dropdown-menu">
					<li><a href="//en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide"><i class="icon-question-sign"></i> Guide</a></li>
					<li><a href="//en.wikipedia.org/wiki/Wikipedia:Username_policy"><i class="icon-warning-sign"></i> Username Policy</a></li>
					<li><a href="#modalFlowchart" role="button" data-toggle="modal"><i class="icon-check"></i> Similar account flowchart</a></li>
					<li><a href="http://webchat.freenode.net/?channels=wikipedia-en-accounts"><i class="icon-comment"></i> Chat</a></li>
				  </ul>
			  </li>
            </ul>
			{if $userid != 0}
			<ul class="nav pull-right">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> {$username} <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li class="nav-header">Account</li>
						<li><a href="{$tsurl}/statistics.php?page=Users&amp;user={$userid}"><i class="icon-tasks"></i> My statistics</a></li>
						<li><a href="{$tsurl}/acc.php?action=prefs"><i class="icon-edit"></i> Edit Preferences</a></li>
						<li class="divider"></li>						
						<li><a href="{$tsurl}/acc.php?action=logout"><i class="icon-lock"></i> Logout</a></li>
					</ul>
				</li>
			</ul>
			{else}
			<p class="navbar-text pull-right">
				Not logged in
			</p>
			{/if}
          </div><!--/.nav-collapse -->{/block}
        </div>
      </div>
    </div>

	{block name="modals"}{include file="modal-flowchart.tpl"}{/block}
	
	{block name="sitenotice"}
    <div class="container-fluid">
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
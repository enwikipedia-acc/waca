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
          <a class="brand" href="{$baseurl}/internal.php">Account Creation Interface</a>
			{block name="navmenu"}{include file="navigation-menu.tpl"}{/block}
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
            {include file="alert.tpl" alertblock=$a->isBlock() alertclosable=$a->isClosable() alerttype=$a->getType()
            alertheader=$a->getTitle() alertmessage=$a->getMessage() }
        {/foreach}
	{/if}

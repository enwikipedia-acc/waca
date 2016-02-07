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
            {block name="navmenu"}
                {include file="navigation-menu.tpl"}
            {/block}
        </div>
    </div>
</div>

{block name="modals"}
    {include file="modal-flowchart.tpl"}
{/block}

<div class="container-fluid">
    {block name="sitenotice"}{/block}

    {block name="content"}This page doesn't do anything. If you see this, and you're not a developer, this is a bug.{/block}

    <hr />

    <footer class="row-fluid">
        <p class="{if $onlineusers == ""}span12{else}span6{/if}"><small>Account Creation Assistance Manager (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>) by <a href="{$baseurl}/internal.php/team">The ACC development team</a> (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>).</small></p>
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

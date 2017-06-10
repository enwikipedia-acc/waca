{* This is the base template for all internal-area pages. *}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{if isset($htmlTitle) && $htmlTitle !== null}{$htmlTitle} :: {/if}Account Creation Interface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- bootstrap styles -->
    <link href="{$baseurl}/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="{$baseurl}/resources/baseStyles.css" rel="stylesheet"/>
    <link href="{$baseurl}/lib/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet"/>
    <link href="{$baseurl}/lib/bootstrap-sortable/css/bootstrap-sortable.css" rel="stylesheet"/>

    <!-- Page-specific extra resources -->
    {foreach from=$extraCss item=css}
        <link href="{$baseurl}{$css}" rel="stylesheet" />
    {/foreach}

    <!-- Our extra styles -->
    <link href="{$baseurl}/resources/styles.css" rel="stylesheet"/>
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

    {block name="content"}
        {$defaultContent|default:"This page doesn't do anything. If you see this, and you're not a developer, this is a bug."}
    {/block}

    <hr/>

    <footer class="row-fluid">
        <p class="span6">
            <small>
                Account Creation Assistance Manager
                (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>)
                by <a href="{$baseurl}/internal.php/team">The ACC development team</a>
                (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>)
            </small>
        </p>
        <p class="span6 text-right">
            <small>
                {if count($onlineusers) > 0}
                    {count($onlineusers)} Account Creator{if count($onlineusers) !== 1}s{/if} currently online (past 5 minutes):
                    {foreach from=$onlineusers item=userObject name=onlineUserLoop}
                    <a href="{$baseurl}/internal.php/statistics/users/detail?user={$userObject->getId()}">
                        {$userObject->getUsername()|escape}</a>{if !$smarty.foreach.onlineUserLoop.last}, {/if}
                    {/foreach}
                {/if}

            </small>
        </p>
    </footer>

</div><!--/.fluid-container-->

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="{$baseurl}/vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
<script src="{$baseurl}/lib/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="{$baseurl}/lib/bootstrap-sortable/js/bootstrap-sortable.js" type="text/javascript"></script>

<!-- Page-specific extra resources -->
{foreach from=$extraJs item=js}
    <script src="{$baseurl}{$js}" type="text/javascript"></script>
{/foreach}

{* initialise the tooltips *}
<script type="text/javascript">
    $(function () {
        $("[rel='tooltip']").tooltip();
    });
    $(function () {
        $("[rel='popover']").popover();
    });
</script>
{* Initialise the type-ahead boxes *}
{$typeAheadBlock}
{if $tailScript}
    <script type="text/javascript">
        {$tailScript}
    </script>
{/if}
</body>
</html>


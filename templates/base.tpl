{* This is the base template for all internal-area pages. *}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{if isset($htmlTitle) && $htmlTitle !== null}{$htmlTitle} :: {/if}Account Creation Interface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- bootstrap styles -->
    <link href="{$baseurl}/resources/generated/bootstrap-{$currentUser->getSkin()|escape|default:'main'}.css?{$resourceCacheEpoch}" rel="stylesheet"/>

    <!-- fontawesome -->
    <link href="{$baseurl}/vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet"/>
    <link href="{$baseurl}/vendor/fortawesome/font-awesome/css/svg-with-js.css" rel="stylesheet" />
</head>

<body>

<nav class="navbar navbar-dark bg-dark fixed-top navbar-expand-lg">
    <a class="navbar-brand" href="{$baseurl}/internal.php">Account Creation Interface</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse" aria-controls=".navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    {block name="navmenu"}
        {include file="navigation-menu.tpl"}
    {/block}
</nav>


{block name="modals"}
    {if ! $currentUser->isCommunityUser()}
        {include file="modals/similar-flowchart.tpl"}
    {/if}
{/block}


<div class="container-fluid">
    {block name="sitenotice"}{/block}

    {block name="content"}
        {$defaultContent|default:"This page doesn't do anything. If you see this, and you're not a developer, this is a bug."}
    {/block}

    <hr/>

    <footer class="row">
        <p class="col-md-6">
            <small>
                Account Creation Assistance Manager
                (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>)
                by <a href="{$baseurl}/internal.php/team">The ACC development team</a>
                (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>)
            </small>
        </p>
        <p class="col-md-6 text-right">
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

{block name="footerjs"}
    {* JS: Placed at the end of the document so the pages load faster *}
    <script src="{$baseurl}/vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
    <script src="{$baseurl}/vendor/fortawesome/font-awesome/js/all.min.js" data-auto-add-css="false" type="text/javascript"></script>
    <script src="{$baseurl}/node_modules/popper.js/dist/umd/popper.min.js" type="text/javascript"></script>
    <script src="{$baseurl}/vendor/twbs/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="{$baseurl}/vendor/twitter/typeahead.js/dist/typeahead.bundle.min.js"></script>
    <script src="{$baseurl}/vendor/drvic10k/bootstrap-sortable/Scripts/bootstrap-sortable.js"></script>
    <script src="{$baseurl}/vendor/drvic10k/bootstrap-sortable/Scripts/moment.min.js"></script>
    <script src="{$baseurl}/resources/global.js?{$resourceCacheEpoch}"></script>

    <!-- Page-specific extra resources -->
    {foreach from=$extraJs item=js}
        <script src="{$baseurl}{$js.path|escape}" type="{$js.type|escape}" integrity="sha256-{$js.integrity}"></script>
    {/foreach}
{/block}
</body>
</html>

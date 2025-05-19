<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request an Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="{$baseurl}/resources/generated/public{$skinBaseline|default:'5'}.css" rel="stylesheet"/>
</head>

<body>

<div class="container">

    {block name="publicheader"}
    <div class="masthead">
        <ul class="nav nav-pills float-right">
            {if $showDebugCssBreakpoints}
                <li class="nav-item">
                    {include file="breakpoints.tpl"}
                </li>
            {/if}
            <li class="nav-item"><a class="nav-link" href="//en.wikipedia.org/wiki/Main_Page">Back to Wikipedia</a></li>
        </ul>
        <h4 class="text-muted">Wikipedia - Request an Account</h4>
    </div>

    <hr>
    {/block}

    {if count($alerts) > 0}
        {foreach $alerts as $a}
            {include file="alert.tpl" alertblock=$a->isBlock() alertclosable=$a->isClosable() alerttype=$a->getType()
            alertheader=$a->getTitle() alertmessage=$a->getMessage() }
        {/foreach}
    {/if}

    {block name="content"}
        {$defaultContent|default:"This page doesn't do anything. If you see this, and you're not a developer, this is a bug."}
    {/block}

    {block name="publicfooter"}
    <hr/>

    <footer class="row">
        <div class="col-md-12">
            <p>
                Account Creation Assistance Manager
                (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>)
                by <a href="{$baseurl}/internal.php/team">The ACC development team</a>
                (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>)
            </p>
            <ul>
                <li><a href="{$baseurl}/index.php/privacy">Privacy Statement</a></li>
                <li><a href="https://wikitech.wikimedia.org/wiki/Wikitech:Cloud_Services_End_User_Terms_of_use">Wikimedia Cloud Services End User Terms of Use</a></li>
            </ul>
        </div>
    </footer>
    {/block}

</div><!--/container-->
{block name="footerjs"}
    {* JS: Placed at the end of the document so the pages load faster *}
    <script src="{$baseurl}/node_modules/jquery/dist/jquery.min.js" type="text/javascript"></script>
    <script src="{$baseurl}/node_modules/bootstrap{$skinBaseline|default:'5'}/dist/js/bootstrap.min.js" type="text/javascript"></script>
{/block}
</body>
</html>

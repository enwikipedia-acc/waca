<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request an Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="{$baseurl}/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{$baseurl}/resources/public.css" rel="stylesheet"/>

</head>

<body>

<div class="container">

    <div class="masthead">
        <ul class="nav nav-pills float-right">
            <li class="nav-item"><a class="nav-link" href="//en.wikipedia.org/wiki/Main_Page">Back to Wikipedia</a></li>
        </ul>
        <h4 class="text-muted">Wikipedia - Request an Account</h4>
    </div>

    <hr>

    {if count($alerts) > 0}
        {foreach $alerts as $a}
            {include file="alert.tpl" alertblock=$a->isBlock() alertclosable=$a->isClosable() alerttype=$a->getType()
            alertheader=$a->getTitle() alertmessage=$a->getMessage() }
        {/foreach}
    {/if}

    {block name="content"}
        {$defaultContent|default:"This page doesn't do anything. If you see this, and you're not a developer, this is a bug."}
    {/block}

    <hr/>

    <footer class="row">
        <p class="col-md-12">
            <small>
                Account Creation Assistance Manager
                (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>)
                by <a href="{$baseurl}/internal.php/team">The ACC development team</a>
                (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>)
            </small>
        </p>
    </footer>

</div><!--/container-->
{block name="footerjs"}
    {* JS: Placed at the end of the document so the pages load faster *}
    <script src="{$baseurl}/vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
    <script src="{$baseurl}/vendor/twbs/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
{/block}
</body>
</html>

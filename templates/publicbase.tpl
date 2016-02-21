<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request an Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="{$baseurl}/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
        body {
            padding-top: 20px;
            padding-bottom: 40px;
        }
    </style>
    <link href="{$baseurl}/lib/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="{$baseurl}/lib/bootstrap-sortable/css/bootstrap-sortable.css" rel="stylesheet"/>

    <!-- Our extra styles -->
    <link href="{$baseurl}/resources/styles.css" rel="stylesheet"/>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="{$baseurl}/lib/bootstrap/js/html5shiv.js"></script>
    <![endif]-->
</head>

<body>

<div class="container">

    <div class="masthead">
        <ul class="nav nav-pills pull-right">
            <li class="active"><a href="#">Request</a></li>
            <li><a href="//en.wikipedia.org/wiki/Main_Page">Back to Wikipedia</a></li>
        </ul>
        <h3 class="muted">Request an Account</h3>
    </div>

    <hr>

    {block name="content"}
        {$defaultContent|default:"This page doesn't do anything. If you see this, and you're not a developer, this is a bug."}
    {/block}

    <hr/>

    <footer class="row-fluid">
        <p class="span12">
            <small>
                Account Creation Assistance Manager
                (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>)
                by <a href="{$baseurl}/internal.php/team">The ACC development team</a>
                (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>)
            </small>
        </p>
    </footer>

</div><!--/container-->

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="{$baseurl}/vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
<script src="{$baseurl}/lib/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="{$baseurl}/lib/bootstrap-sortable/js/bootstrap-sortable.js" type="text/javascript"></script>

{if $tailScript}
    <script type="text/javascript">
        {$tailscript}
    </script>
{/if}
</body>
</html>

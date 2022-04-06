{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <h1>Register for tool access</h1>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-block alert-info">
                <h3>Signing up for Wikipedia?</h3>
                <p class="lead">You're not in the right place! Sorry about that.</p>
                <p>Click the button directly below to go to the right place.</p>
                <p><a class="btn btn-primary btn-large" href="{$baseurl}/index.php">Register for Wikipedia</a></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <p>
                Tool access for a specific wiki allows you to handle requests for that wiki. This requires a lot of
                experience with the policies of your wiki (especially the username policies), and awareness of other
                username polices for other wikis.
            </p>
            <p>Please make sure you refer to the Guide to understand the full requirements.</p>
        </div>
    </div>
    <div class="row">
        {foreach $domains as $d}
            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">{$d->getLongName()|escape} tool access</h3>
                        <p class="card-text">
                            Request access to handle requests from {$d->getLongName()|escape}.
                        </p>
                        {if $allowRegistration && $d->isEnabled()}
                            <p class="card-text"><a class="btn btn-large btn-success" href="{$baseurl}/internal.php/register/standard?d={$d->getShortName()|escape}">Register for tool access</a></p>
                        {else}
                            <p class="card-text">Registration for this tool is currently disabled. Please check back later.</p>
                        {/if}
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{/block}

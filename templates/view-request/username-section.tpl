<h3>Username data for {$requestName|escape}:</h3>
{if $requestIsBlacklisted}
    {include file="alert.tpl" alertblock="1" alerttype="alert-danger" alertclosable="0" alertheader=""
    alertmessage="The requested username is currently blacklisted by the regular expression <code>{$requestBlacklist|escape}</code>."}
{/if}

<div class="linkWrapSection my-4">
    <a id="UsernameUserPage" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/wiki/User:{$requestName|escape:'url'}">
        User page
    </a>
    <a id="UsernameCreationLog" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page={$requestName|escape:'url'}">
        Creation log
    </a>
    <a id="UsernameGlobalRenameLog" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://meta.wikimedia.org/w/index.php?title=Special%3ALog&amp;type=gblrename&amp;oldname={$requestName|escape:'url'}">
        Global Rename log
    </a>
    <a id="UsernameCentralAuth" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$requestName|escape:'url'}">
        Special:CentralAuth
    </a>
    <a id="UsernameUsernameList" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/w/index.php?title=Special%3AListUsers&amp;username={$requestName|escape:'url'}&amp;group=&amp;limit=25">
        Local Username list
    </a>
    <a id="UsernameGlobalUsernameList" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/w/index.php?title=Special%3AGlobalUsers&amp;username={$requestName|escape:'url'}&amp;group=&amp;limit=25">
        Global username list
    </a>
    <a id="UsernameMainspaceSearch" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/w/index.php?title=Special%3ASearch&amp;profile=advanced&amp;search={$requestName|escape:'url'}&amp;fulltext=Search&amp;ns0=1&amp;redirs=1&amp;profile=advanced">
        Wikipedia mainspace search
    </a>
    <a id="UsernameGoogleSearch" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=google&amp;data={$requestName|escape:'url'}">
        Google search
    </a>

</div>

{include file="view-request/antispoof-results.tpl"}
{if $requestIsReservedByMe && !$requestIsClosed && $canCreateLocalAccount}
<h5>CentralAuth</h5>
<a id="UsernameCreateLocal" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
   href="{$mediawikiScriptPath}?title=Special:CreateLocalAccount&amp;target={$requestName|escape:'url'}&amp;wpreason={$createAccountReason|escape:'url'}{$requestId}">
    Force-create local account
</a>
{/if}
<div class="row-fluid">
    <h3>Username data for {$requestName|escape}:</h3>
    {if $requestIsBlacklisted}
        {include file="alert.tpl" alertblock="1" alerttype="alert-error" alertclosable="0" alertheader="Requested Username is Blacklisted"
        alertmessage="The requested username is currently blacklisted by the regular expression <code>{$requestBlacklist|escape}</code>."}
    {/if}

    <div class="linkWrapSection">
        <a id="UsernameUserPage" class="btn btn-small" target="_blank"
           href="https://en.wikipedia.org/wiki/User:{$requestName|escape:'url'}"
           onMouseUp="$('#UsernameUserPage').addClass('btn-visited');">
            User page
        </a>
        <a id="UsernameCreationLog" class="btn btn-small" target="_blank"
           href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page={$requestName|escape:'url'}"
           onMouseUp="$('#UsernameCreationLog').addClass('btn-visited');">
            Creation log
        </a>
        <a id="UsernameSUL" class="btn btn-small" target="_blank"
           href="{$baseurl}/redir.php?tool=sulutil&amp;data={$requestName|escape:'url'}"
           onMouseUp="$('#UsernameSUL').addClass('btn-visited');">
            SUL
        </a>
        <a id="UsernameCentralAuth" class="btn btn-small" target="_blank"
           href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$requestName|escape:'url'}"
           onMouseUp="$('#UsernameCentralAuth').addClass('btn-visited');">
            Special:CentralAuth
        </a>
        <a id="UsernameUsernameList" class="btn btn-small" target="_blank"
           href="https://en.wikipedia.org/w/index.php?title=Special%3AListUsers&amp;username={$requestName|escape:'url'}&amp;group=&amp;limit=1"
           onMouseUp="$('#UsernameUsernameList').addClass('btn-visited');">
            Username list
        </a>
        <a id="UsernameMainspaceSearch" class="btn btn-small" target="_blank"
           href="https://en.wikipedia.org/w/index.php?title=Special%3ASearch&amp;profile=advanced&amp;search={$requestName|escape:'url'}&amp;fulltext=Search&amp;ns0=1&amp;redirs=1&amp;profile=advanced"
           onMouseUp="$('#UsernameMainspaceSearch').addClass('btn-visited');">
            Wikipedia mainspace search
        </a>
        <a id="UsernameGoogleSearch" class="btn btn-small" target="_blank"
           href="{$baseurl}/redir.php?tool=google&amp;data={$requestName|escape:'url'}"
           onMouseUp="$('#UsernameGoogleSearch').addClass('btn-visited');">
            Google search
        </a>
    </div>

    {include file="view-request/antispoof-results.tpl"}
</div>
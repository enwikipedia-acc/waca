<h4>{$devName}</h4>
<ul>
{foreach from=$devInfo key=infoName item=infoContent}
    {if $infoContent != null}
        {if $infoName == "IRC"}
            <li>IRC Name: {$infoContent|escape}</li>
        {/if}
        {if $infoName == "Name"}
            <li>Real Name: {$infoContent|escape}</li>
        {/if}
        {if $infoName == "ToolID"}
            <li>Userpage on tool: <a href="{$baseurl}/statistics.php?page=Users&amp;user={$infoContent|escape:'url'}">Click here</a></li>
        {/if}
        {if $infoName == "wiki"}
            <li>Enwiki Username: <a href="http://en.wikipedia.org/wiki/User:{$infoContent|escape:'url'}">{$infoContent|escape}</a></li>
        {/if}
        {if $infoName == "WWW"}
            <li>Homepage: <a href="{$infoContent}">{$infoContent|escape}</a></li>
        {/if}
        {if $infoName == "Role"}
            <li>Project role: {$infoContent|escape}</li>
        {/if}
        {if $infoName == "Retired"}
            <li>Former role: {$infoContent|escape}</li>
        {/if}
        {if $infoName == "Access"}
            <li>Access: {$infoContent|escape}</li>
        {/if}
        {if $infoName == "Cloak"}
            <li>Cloak: {$infoContent|escape}</li>
        {/if}
        {if $infoName == "Other"}
            <li>Other: {$infoContent|escape}</li>
        {/if}
    {/if}
{/foreach}
</ul>
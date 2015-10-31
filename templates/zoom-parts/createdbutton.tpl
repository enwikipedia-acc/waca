{* If custom create reasons are active, then make the Created button a split button dropdown. *}
{if !empty($createreasons)}
<div class = "btn-group span4">
    <a class="btn btn-success span10" href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email={$createdEmailTemplate->getId()}&amp;sum={$request->getChecksum()}">{$createdEmailTemplate->getName()|escape}</a>
    <button type="button" class="btn btn-success dropdown-toggle span2" data-toggle="dropdown">&nbsp;<span class="caret"></span></button>
    <ul class="dropdown-menu" role="menu">
        {foreach $createreasons as $reason}
            <li><a href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email={$reason->getId()}&amp;sum={$request->getChecksum()}"{if !$currentUser->getAbortPref() && $reason->getJsquestion() != ''} onclick="return confirm('{$reason->getJsquestion()|escape}')"{/if}>{$reason->getName()|escape}</a></li>

        {/foreach}
    </ul>
</div>
{else}
<div class = "span4">
    <a class="btn btn-success span12" href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email={$createdEmailTemplate->getId()}&amp;sum={$request->getChecksum()}">{$createdEmailTemplate->getName()|escape}</a>
</div>
{/if}

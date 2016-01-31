<div class="btn-group span6">
    <button type="button" class="btn btn-warning dropdown-toggle span12"
            data-toggle="dropdown">Decline&nbsp;<span class="caret"></span></button>
    <ul class="dropdown-menu">
        {foreach $declineReasons as $reason}
            <li>
                <a href="{$baseurl}/acc.php?action=done&amp;id={$requestId}&amp;email={$reason->getId()}"
                        {if !$currentUser->getAbortPref() && $reason->getJsquestion() != ''} onclick="return confirm('{$reason->getJsquestion()|escape}')"{/if}>
                    {$reason->getName()|escape}
                </a>
            </li>
        {/foreach}
    </ul>
</div>

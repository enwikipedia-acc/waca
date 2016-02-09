<form class="form-compact" action="{$baseurl}/internal.php/viewRequest/close" method="post">
    <div class="btn-group span6">
        <button type="button" class="btn btn-warning dropdown-toggle span12" data-toggle="dropdown">
            Decline&nbsp;<span class="caret"></span>
        </button>

        <ul class="dropdown-menu">
            {foreach $declineReasons as $reason}
                <li>
                    <button class="btn-link" name="template" value="{$reason->getId()}" type="submit"
                            {if !$currentUser->getAbortPref() && $reason->getJsquestion() != ''}
                        onclick="return confirm('{$reason->getJsquestion()|escape}')"
                            {/if}>
                        {$reason->getName()|escape}
                    </button>
                </li>
            {/foreach}
        </ul>
    </div>
    <input type="hidden" name="request" value="{$requestId}"/>
</form>

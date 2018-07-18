{* If custom create reasons are active, then make the Created button a split button dropdown. *}
<form method="post" action="{$baseurl}/internal.php/viewRequest/close">
    <div class="btn-group col-md-4">
        {if !empty($createReasons)}
            <button class="btn btn-success" type="submit" name="template" value="{$createdId}">
                {$createdName|escape}
            </button>

            <button type="button"
                    class="btn btn-success dropdown-toggle dropdown-toggle-split"
                    data-toggle="dropdown">&nbsp;<span class="caret"></span></button>

            <ul class="dropdown-menu" role="menu">
                {foreach $createReasons as $reason}
                    <li>
                        <button class="btn-link dropdown-item" name="template" value="{$reason->getId()}" type="submit"
                            {if !$currentUser->getAbortPref() && $reason->getJsquestion() != ''}
                        onclick="return confirm('{$reason->getJsquestion()|escape}')"
                            {/if}>
                            {$reason->getName()|escape}
                        </button>
                    </li>
                {/foreach}
            </ul>
        {else}
            <button class="btn btn-success col-md-12" type="submit" name="template" value="{$createdId}">
                {$createdName|escape}
            </button>
        {/if}
    </div>
    <input type="hidden" name="request" value="{$requestId}"/>
    {include file="security/csrf.tpl"}
</form>

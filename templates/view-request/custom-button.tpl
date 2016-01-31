<div class="btn-group span6">
    <a class="btn btn-info span8"
       href="{$baseurl}/acc.php?action=done&amp;id={$requestId}&amp;email=custom">
        Custom
    </a>

    <button type="button" class="btn btn-info dropdown-toggle span4" data-toggle="dropdown">
        &nbsp;<span class="caret"></span>
    </button>

    <ul class="dropdown-menu" role="menu">
        <li class="nav-header">Preload with created reasons:</li>
        {foreach $allCreateReasons as $reason}
            <li>
                <a href="{$baseurl}/acc.php?action=done&amp;id={$requestId}&amp;email=custom&amp;preload={$reason->getId()}">
                    {$reason->getName()|escape}
                </a>
            </li>
        {/foreach}
        <li class="divider"></li>
        <li class="nav-header">Preload with NOT created reasons:</li>
        {foreach $allDeclineReasons as $reason}
            <li>
                <a href="{$baseurl}/acc.php?action=done&amp;id={$requestId}&amp;email=custom&amp;preload={$reason->getId()}">
                    {$reason->getName()|escape}
                </a>
            </li>
        {/foreach}
        <li class="divider"></li>
        <li class="nav-header">Preload with other reasons:</li>
        {foreach $allOtherReasons as $reason}
            <li>
                <a href="{$baseurl}/acc.php?action=done&amp;id={$requestId}&amp;email=custom&amp;preload={$reason->getId()}">
                    {$reason->getName()|escape}
                </a>
            </li>
        {/foreach}
    </ul>
</div>

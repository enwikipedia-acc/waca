<div class="btn-group span6">
    <a class="btn btn-info span8"
       href="{$baseurl}/internal.php/viewRequest/custom?request={$requestId}">
        Custom
    </a>

    <button type="button" class="btn btn-info dropdown-toggle span4" data-toggle="dropdown">
        &nbsp;<span class="caret"></span>
    </button>

    <ul class="dropdown-menu" role="menu">
        <li class="nav-header">Preload with created reasons:</li>
        {foreach $allCreateReasons as $reason}
            <li>
                <a href="{$baseurl}/internal.php/viewRequest/custom?request={$requestId}&amp;template={$reason->getId()}">
                    {$reason->getName()|escape}
                </a>
            </li>
        {/foreach}
        <li class="divider"></li>
        <li class="nav-header">Preload with NOT created reasons:</li>
        {foreach $allDeclineReasons as $reason}
            <li>
                <a href="{$baseurl}/internal.php/viewRequest/custom?request={$requestId}&amp;template={$reason->getId()}">
                    {$reason->getName()|escape}
                </a>
            </li>
        {/foreach}
        <li class="divider"></li>
        <li class="nav-header">Preload with other reasons:</li>
        {foreach $allOtherReasons as $reason}
            <li>
                <a href="{$baseurl}/internal.php/viewRequest/custom?request={$requestId}&amp;template={$reason->getId()}">
                    {$reason->getName()|escape}
                </a>
            </li>
        {/foreach}
    </ul>
</div>

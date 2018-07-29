<div class="col-md-4 offset-md-2">
    <div class="btn-group">
        <a class="btn btn-info"
           href="{$baseurl}/internal.php/viewRequest/custom?request={$requestId}">
            Custom
        </a>

        <button type="button" class="btn btn-info dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
            &nbsp;<span class="caret"></span>
        </button>

        <ul class="dropdown-menu" role="menu">
            <li class="dropdown-header">Preload with created reasons:</li>
            {foreach $allCreateReasons as $reason}
                <li>
                    <a class="dropdown-item" href="{$baseurl}/internal.php/viewRequest/custom?request={$requestId}&amp;template={$reason->getId()}">
                        {$reason->getName()|escape}
                    </a>
                </li>
            {/foreach}
            <li class="dropdown-divider"></li>
            <li class="dropdown-header">Preload with NOT created reasons:</li>
            {foreach $allDeclineReasons as $reason}
                <li>
                    <a class="dropdown-item" href="{$baseurl}/internal.php/viewRequest/custom?request={$requestId}&amp;template={$reason->getId()}">
                        {$reason->getName()|escape}
                    </a>
                </li>
            {/foreach}
            <li class="dropdown-divider"></li>
            <li class="dropdown-header">Preload with other reasons:</li>
            {foreach $allOtherReasons as $reason}
                <li>
                    <a class="dropdown-item" href="{$baseurl}/internal.php/viewRequest/custom?request={$requestId}&amp;template={$reason->getId()}">
                        {$reason->getName()|escape}
                    </a>
                </li>
            {/foreach}
        </ul>
    </div>
</div>

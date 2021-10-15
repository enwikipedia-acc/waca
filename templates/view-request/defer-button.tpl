<form method="post" action="{$baseurl}/internal.php/viewRequest/defer" class="col-md-6">
    <div class="dropright">
        <button type="button" class="btn btn-secondary btn-block dropdown-toggle" data-toggle="dropdown">
            Defer&nbsp;<span class="caret"></span>
        </button>

        <input type="hidden" name="request" value="{$requestId}"/>
        <input type="hidden" name="updateversion" value="{$updateVersion}"/>

        <div class="dropdown-menu">
            {foreach $activeRequestQueues as $queue}
                {if $queue->getApiName() !== $requestQueueApiName}
                    <button class="btn-link dropdown-item" name="target" value="{$queue->getApiName()|escape}" type="submit">
                        Defer to {$queue->getDisplayName()|escape}
                    </button>
                {/if}
            {/foreach}
        </div>
    </div>
    <input type="hidden" name="updateversion" value="{$updateVersion}"/>
    {include file="security/csrf.tpl"}
</form>

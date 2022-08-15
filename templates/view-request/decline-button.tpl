<form class="col-md-3" action="{$baseurl}/internal.php/viewRequest/close" method="post">
    <div class="dropright">
        <button type="button" class="btn btn-warning btn-block dropdown-toggle" data-toggle="dropdown">
            Decline&nbsp;<span class="caret"></span>
        </button>

        <div class="dropdown-menu">
            {foreach $declineReasons as $reason}
                <button class="btn-link dropdown-item jsconfirm" name="template" value="{$reason->getId()}" type="submit"
                        {if !$skipJsAborts && $reason->getJsquestion() != ''}
                            data-template="{$reason->getId()}"
                        {/if}>
                    {$reason->getName()|escape}
                </button>
            {/foreach}
        </div>
    </div>
    <input type="hidden" name="request" value="{$requestId}"/>
    <input type="hidden" name="updateversion" value="{$updateVersion}"/>
    {include file="security/csrf.tpl"}
</form>

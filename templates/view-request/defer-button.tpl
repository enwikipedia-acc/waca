<form method="post" action="{$baseurl}/internal.php/viewRequest/defer" class="col-md-6">
        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
            Defer&nbsp;<span class="caret"></span>
        </button>

        <input type="hidden" name="request" value="{$requestId}"/>
        <input type="hidden" name="updateversion" value="{$updateVersion}"/>

        <ul class="dropdown-menu">
            {foreach $requestStates as $state}
                <li>
                    <button class="btn-link dropdown-item" name="target" value="{$state@key}" type="submit">
                        {$state.deferto|capitalize}
                    </button>
                </li>
            {/foreach}
        </ul>
    {include file="security/csrf.tpl"}
</form>

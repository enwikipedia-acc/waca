<form method="post" action="{$baseurl}/internal.php/viewRequest/defer" class="form-compact">
    <div class="btn-group span6">
        <button type="button" class="btn btn-default dropdown-toggle span12" data-toggle="dropdown">
            Defer&nbsp;<span class="caret"></span>
        </button>

        <input type="hidden" name="request" value="{$requestId}"/>
        <input type="hidden" name="updateversion" value="{$updateVersion}"/>

        <ul class="dropdown-menu">
            {foreach $requestStates as $state}
                <li>
                    <button class="btn-link" name="target" value="{$state@key}" type="submit">
                        {$state.deferto|capitalize}
                    </button>
                </li>
            {/foreach}
        </ul>

    </div>
</form>
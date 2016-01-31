<div class="btn-group span6">
    <button type="button" class="btn btn-default dropdown-toggle span12" data-toggle="dropdown">Defer&nbsp;<span class="caret"></span></button>
    <ul class="dropdown-menu">
    {foreach $requestStates as $state}
        <li><a href="{$baseurl}/acc.php?action=defer&amp;id={$requestId}&amp;target={$state@key}">{$state.deferto|capitalize}</a></li>
    {/foreach}
    </ul>
</div>
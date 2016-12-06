{if !$requestDataCleared}
<h3>Email data for {$requestEmail}</h3>

    <a id="EmailVisit" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=domain&amp;data={$emailurl|escape}" onMouseUp="$('#EmailVisit').addClass('btn-visited');">Visit email domain</a>
    <a id="EmailGoogle" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=google&amp;data={$requestEmail|escape}" onMouseUp="$('#EmailGoogle').addClass('btn-visited');">Google email</a>
    <a id="EmailDomainGoogle" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=google&amp;data={$emailurl|escape}" onMouseUp="$('#EmailDomainGoogle').addClass('btn-visited');">Google email domain</a>
{/if}

<hr/>
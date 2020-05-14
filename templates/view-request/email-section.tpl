{if !$requestDataCleared}
<h3>Email data for {$requestEmail}</h3>
    <a id="EmailVisit" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=domain&amp;data={$emailurl|escape}">Visit email domain</a>
    <a id="EmailGoogle" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=google&amp;data={$requestEmail|escape}">Google email</a>
    <a id="EmailDomainGoogle" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=google&amp;data={$emailurl|escape}">Google email domain</a>
{/if}

<hr/>

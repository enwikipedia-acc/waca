{if $oauth->isFullyLinked()}
    {if $identityExpired}
        <span class="label label-important">CACHE EXPIRED</span>
    {/if}

    {if $identity->getConfirmedEmail()}
        <span class="label label-success">Confirmed email</span>
    {else}
        <span class="label label-important">No confirmed email</span>
    {/if}

    {if $identity->getBlocked()}
        <span class="label label-important">BLOCKED</span>
    {else}
        <span class="label label-success">Not blocked</span>
    {/if}

    <span class="label label-{if $identity->getEditCount() < 1500}warning{else}success{/if}">Edit Count: {$identity->getEditCount()|escape}</span>

    <span class="label label-{if $identity->getAccountAge() < 180}warning{else}success{/if}">Age: {$identity->getAccountAge()} days</span>

    {if $identity->getCheckuser()}
        <span class="label label-success">Checkuser</span>
    {else}
        <span class="label">Not a Checkuser</span>
    {/if}

    <span class="label {if $identity->getGrantCreateEditMovePage()}label-success{/if}">Grant: edit pages</span>
    <span class="label label-{if $identity->getGrantCreateAccount()}success{else}warning{/if}">Grant: create accounts</span>
    <span class="label {if $identity->getGrantHighVolume()}label-success{/if}">Grant: high volume</span>

    <br />
    Cache expires: {DateTime::createFromFormat("U", $identity->getExpirationTime())->format("r")}
{/if}
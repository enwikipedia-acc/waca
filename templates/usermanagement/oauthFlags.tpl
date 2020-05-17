{if $oauth->isFullyLinked()}
    {if $identityExpired}
        <span class="badge badge-danger">CACHE EXPIRED</span>
    {/if}

    {if $identity->getConfirmedEmail()}
        <span class="badge badge-success">Confirmed email</span>
    {else}
        <span class="badge badge-danger">No confirmed email</span>
    {/if}

    {if $identity->getBlocked()}
        <span class="badge badge-danger">BLOCKED</span>
    {else}
        <span class="badge badge-success">Not blocked</span>
    {/if}

    <span class="badge badge-{if $identity->getEditCount() < 1500}warning{else}success{/if}">Edit Count: {$identity->getEditCount()|escape}</span>

    <span class="badge badge-{if $identity->getAccountAge() < 180}warning{else}success{/if}">Age: {$identity->getAccountAge()} days</span>

    {if $identity->getCheckuser()}
        <span class="badge badge-success">Checkuser</span>
    {else}
        <span class="badge badge-secondary">Not a Checkuser</span>
    {/if}

    <span class="badge badge-{if $identity->getGrantCreateEditMovePage()}success{else}danger{/if}">Grant: edit pages</span>
    <span class="badge badge-{if $identity->getGrantCreateAccount()}success{else}danger{/if}">Grant: create accounts</span>
    <span class="badge badge-{if $identity->getGrantHighVolume()}success{else}danger{/if}">Grant: high volume</span>

    <br />
    Cache expires: {DateTime::createFromFormat("U", $identity->getExpirationTime())->format("r")}
{/if}

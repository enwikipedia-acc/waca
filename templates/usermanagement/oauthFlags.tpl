{if $identificationStatus == 'forced-off'}
    <span class="badge badge-danger">Identification forced off</span>
{elseif $identificationStatus == 'forced-on'}
    <span class="badge badge-info">Identification forced on</span>
{elseif $identificationStatus == 'detected'}
    <span class="badge badge-success">Identified</span>
{else}
    <span class="badge badge-warning">Not identified</span>
{/if}

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

    <span class="badge badge-{if $identity->getGrantCreateEditMovePage()}success{else}secondary{/if}">Grant: edit pages</span>
    <span class="badge badge-{if $identity->getGrantCreateAccount()}success{else}secondary{/if}">Grant: create accounts</span>
    <span class="badge badge-{if $identity->getGrantHighVolume()}success{else}secondary{/if}">Grant: high volume</span>

    <br />
    Cache expires: {DateTime::createFromFormat("U", $identity->getExpirationTime())->format("r")}
{/if}

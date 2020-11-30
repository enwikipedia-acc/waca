{if $ban->getName() !== null}
    {if $canSeeNameBan}
        <strong>Username:</strong>&nbsp;{$ban->getName()|escape}
    {else}
        <strong>Username:</strong>&nbsp;<span class="text-muted"><del>(redacted)</del></span>
    {/if}
    <br />
{/if}
{if $ban->getEmail() !== null}
    {if $canSeeEmailBan}
        <strong>Email:</strong>&nbsp;{$ban->getEmail()|escape}
    {else}
        <strong>Email:</strong>&nbsp;<span class="text-muted"><del>(redacted)</del></span>
    {/if}
    <br />
{/if}
{if $ban->getIp() !== null}
    {if $canSeeIpBan}
        <strong>IP address:</strong>&nbsp;<code>{$ban->getIp()|escape}&nbsp;/{$ban->getIpMask()|escape}</code>
    {else}
        <strong>IP address:</strong>&nbsp;<span class="text-muted"><del>(redacted)</del></span>
    {/if}
    <br />
{/if}
{if $ban->getUseragent() !== null}
    {if $canSeeUseragentBan}
        <strong>User agent:</strong>&nbsp;{$ban->getUserAgent()|escape}
    {else}
        <strong>User agent:</strong>&nbsp;<span class="text-muted"><del>(redacted)</del></span>
    {/if}
    <br />
{/if}
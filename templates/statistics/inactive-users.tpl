{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <p>This list contains the usernames of all accounts that have not logged in in the past 90 days.</p>
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr>
            <th>User ID</th>
            <th>Tool Username</th>
            <th>Roles</th>
            <th>On-wiki username</th>
            <th><abbr title="Last login or logged-in page load">Last seen</abbr></th>
            <th><abbr title="Date user was approved for tool access">Approval</abbr></th>
            {if $canDeactivate}
                <th>Deactivate</th>
            {/if}
        </tr>
        </thead>
        <tbody>
        {foreach from=$inactiveUsers item="user"}
                <tr>
                    <td>{$user->getId()}</td>
                    <td>{$user->getUsername()|escape}</td>
                    <td>{$roles[$user->getId()]|escape}</td>
                    <td>{$user->getOnWikiName()|escape}</td>
                    <td>{$user->getLastActive()} <span class="text-muted">{$user->getLastActive()|relativedate}</span></td>
                    <td>{if $user->getApprovalDate() != false}{$user->getApprovalDate()->format("Y-m-d H:i:s")} <span class="text-muted">{$user->getApprovalDate()|relativedate}</span>{/if}</td>
                    {if $canDeactivate}
                        <td>
                            {if ! isset($immune[$user->getId()])}
                                <a class="btn btn-danger btn-sm"
                                   href="{$baseurl}/internal.php/userManagement/deactivate?user={$user->getId()}&amp;preload=Inactive%20for%2090%20or%20more%20days.%20Please%20contact%20a%20tool%20admin%20if%20you%20wish%20to%20come%20back.">
                                    <i class="fas fa-ban"></i> Deactivate
                                </a>
                            {/if}
                        </td>
                    {/if}
                </tr>
        {/foreach}
        </tbody>
    </table>
{/block}

<p>This list contains the usernames of all accounts that have not logged in in the past 90 days.</p>

{if ! $showImmune}<p>Tool root and checkuser-flagged accounts are hidden from this list.</p>{/if}

<table class="table table-striped table-hover table-condensed">
  <thead>
    <tr>
      <th>User ID</th>
      <th>Tool Username</th>
      <th>User access level</th>
      <th>Checkuser?</th>
      <th>enwiki username</th>
      <th><abbr title="Last login or logged-in page load">Last seen</abbr></th>
      <th><abbr title="Date user was approved for tool access">Approval</abbr></th>
      {if $currentUser->isAdmin()}<th>Suspend</th>{/if}
    </tr>
  </thead>
  <tbody>
    {foreach from=$inactiveUsers item="user"}
      {if $user->isCheckuser() && !$showImmune}
      {else}
        <tr>
          <td>{$user->getId()}</td>
          <td>{$user->getUsername()}</td>
          <td>{$user->getStatus()}</td>
          <td>{if $user->isCheckuser()}Yes{else}No{/if}</td>
          <td>{$user->getOnWikiName()}</td>
          <td>{$user->getLastActive()}</td>
          <td>{if $user->getApprovalDate() != false}{$user->getApprovalDate()->format("Y-m-d H:i:s")}{/if}</td>
          {if $currentUser->isAdmin()}
            <td>
              {if (! $user->isCheckuser()) && ($user->getApprovalDate() < $datelimit)}
              <a class="btn btn-danger btn-small" href="{$baseurl}/users.php?suspend={$user->getId()}&amp;preload=Inactive%20for%2090%20or%20more%20days.%20Please%20contact%20a%20tool%20admin%20if%20you%20wish%20to%20come%20back.">
                <i class="icon-ban-circle icon-white"></i> Suspend!
              </a>
              {/if}
            </td>
          {/if}
        </tr>
      {/if}
    {/foreach}
  </tbody>
</table>
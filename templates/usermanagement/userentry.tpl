<tr>
  <th>
    <a href="{$baseurl}/internal.php/statistics/users/detail?user={$user->getId()}">{$user->getUsername()|escape}</a>
  </th>
  <td>
    {if ($user->isOAuthLinked() && $user->getOnWikiName() != "##OAUTH##") || !$user->isOAuthLinked()}
    <a href="//en.wikipedia.org/wiki/User:{$user->getOnWikiName()|escape:'url'}">{$user->getOnWikiName()|escape}</a>
    {/if}
    {if $user->isOAuthLinked()}<span class="badge {if $user->getOnWikiName() == "##OAUTH##"}badge-danger{else}badge-success{/if}">OAuth</span>{/if}
  </td>
  <td>
    {$roles[$user->getId()]|escape}
  </td>
  <td class="table-button-cell">
    {include file="usermanagement/buttons.tpl"}
  </td>
</tr>

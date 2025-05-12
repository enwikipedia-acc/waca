<tr>
  <td data-value="{$user->getUsername()|escape}">
    <strong><a href="{$baseurl}/internal.php/statistics/users/detail?user={$user->getId()}">{$user->getUsername()|escape}</a></strong>
  </td>
  <td>
    <a href="//en.wikipedia.org/wiki/User:{$user->getOnWikiName()|escape:'url'}">{$user->getOnWikiName()|escape}</a>
    {if $oauthStatusMap[$user->getId()] !== 'none'}<span class="badge {if $oauthStatusMap[$user->getId()] === 'partial'}badge-danger{else}badge-success{/if}">OAuth</span>{/if}
  </td>
  <td>
    {$roles[$user->getId()]|escape}
  </td>
  <td class="table-button-cell">
    {include file="usermanagement/buttons.tpl"}
  </td>
</tr>

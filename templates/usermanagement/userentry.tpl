<tr>
  <td data-value="{$user->getUsername()|escape}">
    <strong><a href="{$baseurl}/internal.php/statistics/users/detail?user={$user->getId()}">{$user->getUsername()|escape}</a></strong>
  </td>
  <td>
    <a href="//en.wikipedia.org/wiki/User:{$user->getOnWikiName()|escape:'url'}">{$user->getOnWikiName()|escape}</a>
    {if Waca\Helpers\OAuthUserHelper::userIsFullyLinked($user) || Waca\Helpers\OAuthUserHelper::userIsPartiallyLinked($user) }<span class="badge {if Waca\Helpers\OAuthUserHelper::userIsPartiallyLinked($user)}badge-danger{else}badge-success{/if}">OAuth</span>{/if}
  </td>
  <td>
    {$roles[$user->getId()]|escape}
  </td>
  <td class="table-button-cell">
    {include file="usermanagement/buttons.tpl"}
  </td>
</tr>

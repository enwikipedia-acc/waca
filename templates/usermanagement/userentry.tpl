<tr>
  <th>
    <a href="{$baseurl}/internal.php/statistics/users/detail?user={$user->getId()}">{$user->getUsername()|escape}</a>
  </th>
  <td>
    <a href="//en.wikipedia.org/wiki/User:{$user->getOnWikiName()|escape:'url'}">{$user->getOnWikiName()|escape}</a>
    {if Waca\Helpers\OAuthUserHelper::userIsFullyLinked($user) || Waca\Helpers\OAuthUserHelper::userIsPartiallyLinked($user) }<span class="label {if Waca\Helpers\OAuthUserHelper::userIsPartiallyLinked($user)}label-important{else}label-success{/if}">OAuth</span>{/if}
  </td>
  <td>
    {$roles[$user->getId()]|escape}
  </td>
  <td>
    {include file="usermanagement/buttons.tpl"}
  </td>
</tr>
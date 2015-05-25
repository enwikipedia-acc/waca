<tr>
  <th>
    <a href="{$baseurl}/statistics.php?page=Users&amp;user={$user->getId()}">{$user->getUsername()|escape}</a>
  </th>
  <td>
    {if ($user->isOAuthLinked() && $user->getOnWikiName() != "##OAUTH##") || !$user->isOAuthLinked()}
    <a href="//en.wikipedia.org/wiki/User:{$user->getOnWikiName()|escape:'url'}">{$user->getOnWikiName()|escape}</a>
    {/if}
    {if $user->isOAuthLinked()}<span class="label {if $user->getOnWikiName() == "##OAUTH##"}label-important{else}label-success{/if}">OAuth</span>{/if}
  </td>
  <td>
    {include file="usermanagement/buttons.tpl"}
  </td>
</tr>
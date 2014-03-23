<tr>
	<td>
    <a class="btn btn-small{if $r->hasComments() == true} btn-info{/if}" href="{$tsurl}/acc.php?action=zoom&amp;id={$r->getId()}"><i class="{if $r->hasComments() == true}icon-white{else}icon-black{/if} icon-search"></i><span class="visible-desktop">&nbsp;{$r->getId()}</span></a>
  </td>
  {if $showStatus}
    <td>{$r->getStatus()}</td>
  {/if}
	<td>{if $currentUser->isAdmin() || $currentUser->isCheckUser() == true}<a href="mailto:{$r->getEmail()}" target="_blank">{$r->getEmail()}</a>&nbsp;<span class="badge{if count($r->getRelatedEmailRequests()) > 0} badge-important{/if}">{count($r->getRelatedEmailRequests())}</span><span class="hidden-desktop"><br /><a href="https://en.wikipedia.org/wiki/User_talk:{$r->getTrustedIp()}" target="_blank">{$r->getTrustedIp()}</a>&nbsp;<span class="badge {if count($r->getRelatedIpRequests()) > 0} badge-important{/if}">{count($r->getRelatedIpRequests())}</span>{/if}
		<span class="visible-phone"><br /><a href="https://en.wikipedia.org/wiki/User:{$r->getName()}" target="_blank">{$r->getName()}</a></span></span></td>
	<td>{if $currentUser->isAdmin() || $currentUser->isCheckUser() == true}<span class="visible-desktop"><a href="https://en.wikipedia.org/wiki/User_talk:{$r->getTrustedIp()}" target="_blank">{$r->getTrustedIp()}</a>&nbsp;<span class="badge {if count($r->getRelatedIpRequests()) > 0} badge-important{/if}">{count($r->getRelatedIpRequests())}</span></span>{/if}</td>
	<td><span class="hidden-phone"><a href="https://en.wikipedia.org/wiki/User:{$r->getName()}" target="_blank">{$r->getName()}</a></span></td>
	<td><span class="visible-desktop">
    <a rel="tooltip" href="#rqtime{$r->getId()}" title="{$r->getDate()}" data-toggle="tooltip" class="plainlinks" id="#rqtime{$r->getId()}">{$r->getDate()|relativedate}</a>
  </span></td>
	<td>{if $currentUser->isAdmin() || $currentUser->isCheckuser() }<div class="btn-group hidden-phone"><a class="btn dropdown-toggle btn-small btn-danger" data-toggle="dropdown" href="#">
    <i class="icon-white icon-ban-circle"></i>&nbsp;Ban&nbsp;<span class="caret"></span></a><ul class="dropdown-menu"><li><a href="{$tsurl}/acc.php?action=ban&amp;ip={$r->getId()}">IP</a></li><li><a href="{$tsurl}/acc.php?action=ban&amp;email={$r->getId()}">Email</a></li><li><a href="{$tsurl}/acc.php?action=ban&amp;name={$r->getId()}">Name</a></li></ul></div>{/if}</td>
	<td>
    {if $r->getReserved() != false}
    {if $r->getReserved() == $currentUser->getId()}
  </td>
  <td>
    <a class="btn btn-small btn-inverse" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
      <i class="icon-white icon-star"></i>&nbsp;Unreserve
    </a>
  {else}
    <span class="visible-desktop">Being handled by {$r->getReservedObject()->getUsername()}</span>
  </td>
  <td>
    {if $currentUser->isAdmin() || $currentUser->isCheckUser() }
    <a class="btn btn-small btn-warning visible-desktop" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
      <i class="icon-white icon-trash"></i>&nbsp;Force break
    </a>
    <a class="btn btn-small btn-warning hidden-desktop" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
      <i class="icon-white icon-trash"></i>&nbsp; {$r->getReservedObject()->getUsername()}</a>
    {else}
      <span class="hidden-desktop">{$r->getReservedObject()->getUsername()}</span>
    {/if}
  {/if}
{else}
  </td>
  <td>
    <a class="btn btn-small btn-success" href="{$tsurl}/acc.php?action=reserve&amp;resid={$r->getId()}">
      <i class="icon-white icon-star-empty"></i>&nbsp;Reserve
    </a>
{/if}
  </td>
</tr>

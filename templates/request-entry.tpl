{function emailDisplay}
<a href="mailto:{$r->getEmail()|escape:'url'}" target="_blank">{$r->getEmail()|escape}</a>&nbsp;<span class="badge{if count($r->getRelatedEmailRequests()) > 0} badge-important{/if}">{count($r->getRelatedEmailRequests())}</span>
{/function}

{function ipDisplay}
<a href="https://en.wikipedia.org/wiki/User_talk:{$r->getTrustedIp()}" target="_blank">{$r->getTrustedIp()}</a>&nbsp;<span class="badge {if count($r->getRelatedIpRequests()) > 0} badge-important{/if}">{count($r->getRelatedIpRequests())}</span>
{/function}

{function usernameDisplay}
<a href="https://en.wikipedia.org/wiki/User:{$r->getName()|escape:'url'}" target="_blank">{$r->getName()|escape}</a>
{/function}

{function timeDisplay}
<a rel="tooltip" href="#rqtime{$r->getId()}" title="{$r->getDate()}" data-toggle="tooltip" class="plainlinks" id="#rqtime{$r->getId()}">{$r->getDate()|relativedate}</a>
{/function}

<tr>
  
	<td>
    <a class="btn btn-small{if $r->hasComments() == true} btn-info{/if}" href="{$baseurl}/acc.php?action=zoom&amp;id={$r->getId()}"><i class="{if $r->hasComments() == true}icon-white{else}icon-black{/if} icon-search"></i><span class="visible-desktop">&nbsp;{$r->getId()}</span></a>
  </td>
  
  {if $showStatus}
    <td>{$r->getStatus()}</td>
  {/if}
  
  {* Email (and IP, username on smaller screens) *}
  <td>
    {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
      {emailDisplay}
      <span class="hidden-desktop">
        <br />
        {ipDisplay}
      </span>
    {/if}
    {if $currentUser->isAdmin() || $currentUser->isCheckUser()}<br class="visible-phone" />{/if}{* Hide the newline if you can't see the data above it *}
    <span class="hidden-desktop hidden-tablet">
      {usernameDisplay}
    </span>
  </td>

  {* IP Address *}
  <td>
    {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
      <span class="visible-desktop">
        {ipDisplay}
      </span>
    {/if}
  </td>
	
  {* Username *}
  <td>
    <span class="hidden-phone">
      {usernameDisplay}
    </span>
  </td>
  
  {* Request Time *}
	<td>
    <span class="visible-desktop">
      {timeDisplay}
    </span>
  </td>
  
  {* Bans *}
	<td>
    {if $currentUser->isAdmin() || $currentUser->isCheckuser() }
      <div class="btn-group hidden-phone">
        <a class="btn dropdown-toggle btn-small btn-danger" data-toggle="dropdown" href="#">
          <i class="icon-white icon-ban-circle"></i>&nbsp;Ban&nbsp;<span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
          <li><a href="{$baseurl}/acc.php?action=ban&amp;ip={$r->getId()}">IP</a></li>
          <li><a href="{$baseurl}/acc.php?action=ban&amp;email={$r->getId()}">Email</a></li>
          <li><a href="{$baseurl}/acc.php?action=ban&amp;name={$r->getId()}">Name</a></li>
        </ul>
      </div>
    {/if}
  </td>
	
  {* Reserve status *}
  <td>
    {if $r->getReserved() != false && $r->getReserved() != $currentUser->getId()}
      <span class="visible-desktop">Being handled by {$r->getReservedObject()->getUsername()|escape}</span>
    {/if}
  </td>
  
  {* Reserve Button *}
  <td>
    {if $r->getReserved() == false}
    
      <a class="btn btn-small btn-success" href="{$baseurl}/acc.php?action=reserve&amp;resid={$r->getId()}">
        <i class="icon-white icon-star-empty"></i>&nbsp;Reserve
      </a>
    
    {else}
    
      {if $r->getReserved() == $currentUser->getId()}
      
        <a class="btn btn-small btn-inverse" href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
          <i class="icon-white icon-star"></i>&nbsp;Unreserve
        </a>
    
      {else}
      
        {if $currentUser->isAdmin() || $currentUser->isCheckUser() }
          <a class="btn btn-small btn-warning visible-desktop" href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
            <i class="icon-white icon-trash"></i>&nbsp;Force break
          </a>    
          <a class="btn btn-small btn-warning hidden-desktop" href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
            <i class="icon-white icon-trash"></i>&nbsp; {$r->getReservedObject()->getUsername()|escape}
          </a>
        {else}
          <span class="hidden-desktop">{$r->getReservedObject()->getUsername()|escape}</span>
        {/if}
      
      {/if}
      
    {/if}
  </td>
</tr>

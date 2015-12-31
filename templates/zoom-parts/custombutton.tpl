<div class = "btn-group span6">
  <a class="btn btn-info span8" href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}">Custom</a>
  <button type="button" class="btn btn-info dropdown-toggle span4" data-toggle="dropdown">
    &nbsp;<span class="caret"></span>
  </button>
  <ul class="dropdown-menu" role="menu">
	  <li class="nav-header">Preload with created reasons:</li>
    {foreach $allcreatereasons as $reason}
    <li>
      <a href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}&preload={$reason->getId()}">{$reason->getName()|escape}</a>
    </li>
    {/foreach}
    <li class="divider"></li>
  	<li class="nav-header">Preload with NOT created reasons:</li>
    {foreach $alldeclinereasons as $reason}
    <li>
      <a href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}&preload={$reason->getId()}">{$reason->getName()|escape}</a>
    </li>
    {/foreach}
  	<li class="divider"></li>
  	<li class="nav-header">Preload with other reasons:</li>
  	{foreach $allotherreasons as $reason}
  	<li>
  		<a href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}&preload={$reason->getId()}">{$reason->getName()|escape}</a>
  	</li>
  	{/foreach}
  </ul>
</div>

<div class = "btn-group span6">
  <a class="btn btn-info span8" href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}">Custom</a>
  <button type="button" class="btn btn-info dropdown-toggle span4" data-toggle="dropdown">
    &nbsp;<span class="caret"></span>
  </button>
  <ul class="dropdown-menu" role="menu">
    {foreach $allcreatereasons as $reason}
    <li>
      <a href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}&preload={$reason->getId()}">{$reason->getName()}</a>
    </li>
    {/foreach}
    <li class="divider"></li>
    {foreach $alldeclinereasons as $reason}
    <li>
      <a href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}&preload={$reason->getId()}">{$reason->getName()}</a>
    </li>
    {/foreach}
  </ul>
</div>
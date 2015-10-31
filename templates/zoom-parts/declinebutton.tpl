<div class="btn-group span6">
  <button type="button" class="btn btn-warning dropdown-toggle span12" data-toggle="dropdown">Decline&nbsp;<span class="caret"></span></button>
  <ul class="dropdown-menu">
    {foreach $declinereasons as $reason}
      <li><a href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email={$reason->getId()}&amp;sum={$request->getChecksum()}"{if !$currentUser->getAbortPref() && $reason->getJsquestion() != ''} onclick="return confirm('{$reason->getJsquestion()|escape}')"{/if}>{$reason->getName()|escape}</a></li>
    {/foreach}
  </ul>
</div>

<!-- tpl:zoom-parts/request-actions.tpl -->
<div class="row-fluid">
  {if $showinfo == true && $isprotected == false && $request->getReserved() != 0}
  <a class="btn btn-primary span12" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:UserLogin/signup&amp;wpName={$usernamerawunicode|escape:'url'}&amp;wpEmail={$request->getEmail()|escape:'url'}&amp;wpReason=Requested%20account%20at%20%5B%5BWP%3AACC%5D%5D%2C%20request%20%23{$request->getId()}&amp;wpCreateaccountMail=true"{if !$currentUser->getAbortPref() && $createdEmailTemplate->getJsquestion() != ''} onclick="return confirm('{$createdEmailTemplate->getJsquestion()}')"{/if}>Create account</a>
  {/if}
</div>

<hr />

<div class="row-fluid">
  {if $request->getReserved() == $currentUser->getId()}
    <div class="span8">
      <form action="{$tsurl}/acc.php?action=sendtouser&amp;hash={$request->getChecksum()}" method="post" class="form-inline">
        <input type="hidden" name="id" value="{$request->getId()}" />
        <div class="row-fluid">
          <input type="text" required="true" placeholder="Send reservation to another user..." name="user" data-provide="typeahead" data-items="4" data-source='{$jsuserlist}' class="span8"/>
          <input class="btn span4" type="submit" value="Send Reservation" />
        </div>
      </form>
    </div>
    <a class="btn btn-inverse span4" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$request->getId()}">Break reservation</a>
  {elseif $currentUser->isAdmin() && $request->getReserved() != 0}
    <a class="btn span6 offset6 btn-warning" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$request->getId()}">Force break</a>
  {/if}
  {if $request->getReserved() == 0}
    <a class="btn span6 offset6 btn-success" href="{$tsurl}/acc.php?action=reserve&amp;resid={$request->getId()}">Reserve</a>
  {/if}
</div> <!-- /row-fluid -->

{if $isprotected == false}
  <hr />
  <div class="row-fluid">
    {if $request->getReserved() != 0}
    {* If custom create reasons are active, then make the Created button a split button dropdown. *}
      {if !empty($createreasons)}
      <div class = "btn-group span4">
        <a class="btn btn-success span10" href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email={$createdEmailTemplate->getId()}&amp;sum={$request->getChecksum()}">{$createdEmailTemplate->getName()}</a>
        <button type="button" class="btn btn-success dropdown-toggle span2" data-toggle="dropdown">&nbsp;<span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
        {foreach $createreasons as $reason}
        	<li><a href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email={$reason->getId()}&amp;sum={$request->getChecksum()}"{if !$currentUser->getAbortPref() && $reason->getJsquestion() != ''} onclick="return confirm('{$reason->getJsquestion()}')"{/if}>{$reason->getName()}</a></li>
        {/foreach}
        </ul>
      </div>
      {else}
      <div class = "span4">
      <a class="btn btn-success span12" href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email={$createdEmailTemplate->getId()}&amp;sum={$request->getChecksum()}">{$createdEmailTemplate->getName()}</a>
      </div>
      {/if}
      <div class = "span4">
        <div class="btn-group span6">
          <button type="button" class="btn btn-warning dropdown-toggle span12" data-toggle="dropdown">Decline&nbsp;<span class="caret"></span></button>
          <ul class="dropdown-menu">
          {foreach $declinereasons as $reason}
            <li><a href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email={$reason->getId()}&amp;sum={$request->getChecksum()}"{if !$currentUser->getAbortPref() && $reason->getJsquestion() != ''} onclick="return confirm('{$reason->getJsquestion()}')"{/if}>{$reason->getName()}</a></li>
          {/foreach}
          </ul>
        </div>
        <div class = "btn-group span6">
          <a class="btn btn-info span8" href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}">Custom</a>
          <button type="button" class="btn btn-info dropdown-toggle span4" data-toggle="dropdown">&nbsp;<span class="caret"></span></button>
          <ul class="dropdown-menu" role="menu">
            {foreach $createreasons as $reason}
        	  <li><a href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}&preload={$reason->getId()}">{$reason->getName()}</a></li>
            {/foreach}
            <li class="divider"></li>
            {foreach $declinereasons as $reason}
            <li>
              <a href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=custom&amp;sum={$request->getChecksum()}&preload={$reason->getId()}">{$reason->getName()}</a>
            </li>
            {/foreach}
          </ul>
        </div>
      </div> <!-- /span4 -->
    {/if}
                  
    <div class="span4{if $request->getReserved() == 0} offset8{/if}">
      {if !array_key_exists($request->getStatus(), $requeststates)}
        <a class="btn span12" href="{$tsurl}/acc.php?action=defer&amp;id={$request->getId()}&amp;sum={$request->getChecksum()}&amp;target={$defaultstate}">Reset request</a>
      {else}
        <div class="btn-group span6">
          <button type="button" class="btn btn-default dropdown-toggle span12" data-toggle="dropdown">Defer&nbsp;<span class="caret"></span></button>
          <ul class="dropdown-menu">
            {foreach $requeststates as $state}
              <li><a href="{$tsurl}/acc.php?action=defer&amp;id={$request->getId()}&amp;sum={$request->getChecksum()}&amp;target={$state@key}">{$state.deferto|capitalize}</a></li>
            {/foreach}
          </ul>
        </div>
      {/if}
                  
      {if $request->getStatus() != "Closed"}
        <a class="btn btn-inverse span6" href="{$tsurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=0&amp;sum={$request->getChecksum()}">Drop</a>
      {/if}
    </div>
  </div>
{/if}
              
{if $currentUser->isAdmin() || $currentUser->isCheckuser()}
  <hr />
  <div class="row-fluid">
    <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;name={$request->getId()}">Ban Username</a>
    <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;email={$request->getId()}">Ban Email</a>
    <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;ip={$request->getId()}">Ban IP</a>
  </div>
{/if}

<hr />
<!-- /tpl:zoom-parts/request-actions.tpl -->

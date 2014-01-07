<!-- tpl:zoom-parts/request-actions.tpl -->
<div class="row-fluid">
  {if $showinfo == true && $isprotected == false && $isreserved == true}
  <a class="btn btn-primary span12" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:UserLogin/signup&amp;wpName={$usernamerawunicode|escape:'url'}&amp;wpEmail={$email|escape:'url'}&amp;wpReason={$createreason|escape:'url'}&amp;wpCreateaccountMail=true"{if !$currentUser->getAbortPref() && $createdquestion != ''} onclick="return confirm('{$createdquestion}')"{/if}>Create account</a>
  {/if}
</div>

<hr />

<div class="row-fluid">
  {if $reserved == $currentUser->getUsername()}
    <div class="span8">
      <form action="{$tsurl}/acc.php?action=sendtouser&amp;hash={$checksum}" method="post" class="form-inline">
        <input type="hidden" name="id" value="{$id}" />
        <div class="row-fluid">
          <input type="text" required="true" placeholder="Send reservation to another user..." name="user" data-provide="typeahead" data-items="4" data-source='{$jsuserlist}' class="span8"/>
          <input class="btn span4" type="submit" value="Send Reservation" />
        </div>
      </form>
    </div>
    <a class="btn btn-inverse span4" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$id}">Break reservation</a>
  {elseif $currentUser->isAdmin() && $isreserved}
    <a class="btn span6 offset6 btn-warning" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$id}">Force break</a>
  {/if}
  {if !$isreserved}
    <a class="btn span6 offset6 btn-success" href="{$tsurl}/acc.php?action=reserve&amp;resid={$id}">Reserve</a>
  {/if}
</div> <!-- /row-fluid -->

{if $isprotected == false}
  <hr />
  <div class="row-fluid">
    {if $isreserved == true}
    {* If custom create reasons are active, then make the Created button a split button dropdown. *}
      {if !empty($createreasons)}
      <div class = "btn-group span4">
        <a class="btn btn-success span10" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email={$createdid}&amp;sum={$checksum}">{$createdname}</a>
        <button type="button" class="btn btn-success dropdown-toggle span2" data-toggle="dropdown"><span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
        {foreach $createreasons as $reason}
        	<li><a href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email={$reason@key}&amp;sum={$checksum}"{if !$currentUser->getAbortPref() && $reason.question != ''} onclick="return confirm('{$reason.question}')"{/if}>{$reason.name}</a></li>
        {/foreach}
        </ul>
      </div>
      {else}
      <div class = "span4">
      <a class="btn btn-success span12" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email={$createdid}&amp;sum={$checksum}">{$createdname}</a>
      </div>
      {/if}
      <div class = "span4">
        <div class="btn-group span6">
          <button type="button" class="btn btn-warning dropdown-toggle span12" data-toggle="dropdown">Decline<span class="caret"></span></button>
          <ul class="dropdown-menu">
          {foreach $declinereasons as $reason}
            <li><a href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email={$reason@key}&amp;sum={$checksum}"{if !$currentUser->getAbortPref() && $reason.question != ''} onclick="return confirm('{$reason.question}')"{/if}>{$reason.name}</a></li>
          {/foreach}
          </ul>
        </div>
        <a class="btn btn-info span6" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=custom&amp;sum={$checksum}">Custom</a>
      </div> <!-- /span4 -->
    {/if}
                  
    <div class="span4{if !$isreserved} offset8{/if}">
      {if !array_key_exists($type, $requeststates)}
        <a class="btn span12" href="{$tsurl}/acc.php?action=defer&amp;id={$id}&amp;sum={$checksum}&amp;target={$defaultstate}">Reset request</a>
      {else}
        <div class="btn-group span6">
          <button type="button" class="btn btn-default dropdown-toggle span12" data-toggle="dropdown">Defer&nbsp;<span class="caret"></span></button>
          <ul class="dropdown-menu">
            {foreach $requeststates as $state}
              <li><a href="{$tsurl}/acc.php?action=defer&amp;id={$id}&amp;sum={$checksum}&amp;target={$state@key}">{$state.deferto|capitalize}</a></li>
            {/foreach}
          </ul>
        </div>
      {/if}
                  
      {if !$isclosed}
        <a class="btn btn-inverse span6" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=0&amp;sum={$checksum}">Drop</a>
      {/if}
    </div>
  </div>
{/if}
              
{if $currentUser->isAdmin()}
  <hr />
  <div class="row-fluid">
    <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;name={$id}">Ban Username</a>
    <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;email={$id}">Ban Email</a>
    <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;ip={$id}">Ban IP</a>
  </div>
{/if}

<hr />
<!-- /tpl:zoom-parts/request-actions.tpl -->

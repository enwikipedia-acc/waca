<!-- tpl:zoom-parts/request-actions.tpl -->
{if $showinfo == true && $isprotected == false && $request->getReserved() != 0}
<div class="row-fluid">
  <a class="btn btn-primary span12" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:UserLogin/signup&amp;wpName={$usernamerawunicode|escape:'url'}&amp;email={$request->getEmail()|escape:'url'}&amp;reason=Requested%20account%20at%20%5B%5BWP%3AACC%5D%5D%2C%20request%20%23{$request->getId()}&amp;wpCreateaccountMail=true"{if !$currentUser->getAbortPref() && $createdEmailTemplate->getJsquestion() != ''} onclick="return confirm('{$createdEmailTemplate->getJsquestion()}')"{/if}>Create account</a>
</div>
<hr />
{/if}

<div class="row-fluid">
  <h5>Reservation</h5>
</div> <!-- /row-fluid -->
<div class="row-fluid">
  
  <div class="span8">
    {if $request->getReserved() == $currentUser->getId()}
    <form action="{$baseurl}/acc.php?action=sendtouser&amp;hash={$request->getChecksum()}" method="post" class="form-inline">
      <input type="hidden" name="id" value="{$request->getId()}" />
      <div class="row-fluid">
        <input type="text" required="true" placeholder="Send reservation to another user..." name="user" data-provide="typeahead" data-items="4" class="span8 username-typeahead" {if $request->getReserved() != $currentUser->getId()}disabled={/if}/>
        <input class="btn span4" type="submit" value="Send Reservation"/>
      </div>
    </form>
    {/if}
  </div>
  {if $request->getReserved() == $currentUser->getId()}
  <a class="btn btn-inverse span4" href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$request->getId()}">Break reservation</a>
  {elseif $currentUser->isAdmin() && $request->getReserved() != 0}
    <a class="btn span4 btn-warning" href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$request->getId()}">Force break</a>
  {/if}
  {if $request->getReserved() == 0}
    <a class="btn span4 btn-success" href="{$baseurl}/acc.php?action=reserve&amp;resid={$request->getId()}">Reserve</a>
  {/if}
</div> <!-- /row-fluid -->
<hr />


{if $isprotected == false}
<div class="row-fluid">
  <h5>Request status:</h5>
</div> <!-- /row-fluid -->
<div class="row-fluid">
    {if $request->getReserved() != 0}
      {include file="zoom-parts/createdbutton.tpl"}
      <div class = "span4">
        {include file="zoom-parts/declinebutton.tpl"}{include file="zoom-parts/custombutton.tpl"}
      </div> <!-- /span4 -->
    {/if}
                  
    <div class="span4{if $request->getReserved() == 0} offset8{/if}">
      {if !array_key_exists($request->getStatus(), $requeststates)}
        <a class="btn span12" href="{$baseurl}/acc.php?action=defer&amp;id={$request->getId()}&amp;sum={$request->getChecksum()}&amp;target={$defaultstate}">Reset request</a>
      {else}
        {include file="zoom-parts/deferbutton.tpl"}
      {/if}
                  
      {if $request->getStatus() != "Closed"}
        <a class="btn btn-inverse span6" href="{$baseurl}/acc.php?action=done&amp;id={$request->getId()}&amp;email=0&amp;sum={$request->getChecksum()}">Drop</a>
      {/if}
    </div>
  
</div>
<hr />
{/if}

{if $currentUser->isAdmin() || $currentUser->isCheckuser()}
  <div class="row-fluid">
    <h5>Ban</h5>
  </div> <!-- /row-fluid -->
  <div class="row-fluid">
    <a class="btn btn-danger span4" href="{$baseurl}/acc.php?action=ban&amp;name={$request->getId()}">Ban Username</a>
    <a class="btn btn-danger span4" href="{$baseurl}/acc.php?action=ban&amp;email={$request->getId()}">Ban Email</a>
    <a class="btn btn-danger span4" href="{$baseurl}/acc.php?action=ban&amp;ip={$request->getId()}">Ban IP</a>
  </div>
{/if}

<hr />
<!-- /tpl:zoom-parts/request-actions.tpl -->

<!-- tpl:zoom-base.tpl -->
<div class="row-fluid">
  <!-- request details -->
  <div class="span6 container-fluid">
    {include file="zoom-parts/request-info.tpl" showLink="true"}
    <hr />
    {include file="zoom-parts/request-actions.tpl"}
  </div>
  <div class="span6 container-fluid">
    {include file="zoom-parts/request-log.tpl"}
  </div>
</div><!--/row-->    

<hr />
{if $showinfo == true}
  {include file="zoom-parts/ip-section.tpl"}
{/if}

{include file="zoom-parts/username-section.tpl"}

<div class="row-fluid">
  <div class="span6">
    <h3>Other requests from {if $showinfo == true}{$request->getEmail()|escape}{else}this email address{/if}</h3>
    {if $request->getEmail() == "acc@toolserver.org"}
      <p class="muted">Email information cleared</p>
    {elseif count($request->getRelatedEmailRequests()) == 0}
      <p class="muted">None detected</p>
    {else}
      {include file="zoom-parts/relatedrequests.tpl" requests=$request->getRelatedEmailRequests()}
    {/if}
  </div>
  <div class="span6">
    <h3>Other requests from {if $showinfo == true}{$request->getTrustedIp()|escape}{else}this IP address{/if}</h3>
    {if $request->getTrustedIp() == "127.0.0.1"}
      <p class="muted">IP information cleared</p>
    {elseif count($request->getRelatedIpRequests()) == 0}
      <p class="muted">None detected</p>
    {else}
      {include file="zoom-parts/relatedrequests.tpl" requests=$request->getRelatedIpRequests()}
    {/if}
  </div>
</div>
<!-- /tpl:zoom-base.tpl -->
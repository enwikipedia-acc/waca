<!-- tpl:request-zoom.tpl -->
<div class="row-fluid">
  <!-- page header -->
  <div class="span12">
    <h2>Details for Request #{$request->getId()}:</h2>
  </div>
</div><!--/row-->   

<div class="row-fluid">
  <!-- request details -->
  <div class="span6 container-fluid">
    {include file="zoom-parts/request-info.tpl"}
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
    <h3>Other requests from {if $showinfo == true}{$request->getEmail()}{else}this email address{/if}</h3>
    {if $request->getEmail() == "acc@toolserver.org"}
      <p class="muted">Email information cleared</p>
    {elseif $numemail == 0}
      <p class="muted">None detected</p>
    {else}
      <table class="table table-condensed table-striped">
        {foreach $otheremail as $others}
          <tr>
            <td>{$others.date}</td>
            <td><a target="_blank" href="{$tsurl}/acc.php?action=zoom&amp;id={$others.id}">{$others.name}</a></td>
          </tr>
        {/foreach}
      </table>
    {/if}
  </div>
  <div class="span6">
      <p class="muted">IP information cleared</p>
    <h3>Other requests from {if $showinfo == true}{$request->getTrustedIp()}{else}this IP address{/if}</h3>
    {if $request->getTrustedIp() == "127.0.0.1"}
    {elseif $numip == 0}
      <p class="muted">None detected</p>
    {else}
      <table class="table table-condensed table-striped">
        {foreach $otherip as $others}
          <tr>
            <td>{$others.date}</td>
            <td><a target="_blank" href="{$tsurl}/acc.php?action=zoom&amp;id={$others.id}">{$others.name}</a></td>
          </tr>
        {/foreach}
      </table>
    {/if}
  </div>
</div>
<!-- /tpl:request-zoom.tpl -->
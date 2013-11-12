<div class="row-fluid">
	<!-- page header -->
	<div class="span12">
		<h3>Details for Request #{$id}:</h3>
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
    <h4>Other requests from {if $showinfo == true}{$email}{else}this email address{/if}</h4>
    {if $email == "acc@toolserver.org"}
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
    <h4>Other requests from {if $showinfo == true}{$ip}{else}this IP address{/if}</h4>
    {if $ip == "127.0.0.1"}
      <p class="muted">IP information cleared</p>
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
        

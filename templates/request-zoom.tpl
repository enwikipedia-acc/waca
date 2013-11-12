<div class="row-fluid">
	<!-- page header -->
	<div class="span12">
		<h3>Details for Request #{$id}:</h3>
	</div>
</div><!--/row-->	 
        
	  <hr />
     {if $showinfo == true}
     {if $proxyip != NULL}
        <div class="row-fluid">
            <h4>IP Address data:</h4>
            <p class="muted">This request came from {$ip}, stating it was forwarded for {$proxyip} via the X-Forwared-For HTTP header. The IP address which Wikipedia will see is the first "untrusted" IP address in the list below. Links are shown for all addresses starting from where the chain becomes untrusted. IPs past the first untrusted address are not trusted to be correct. Please see the <a href="https://toolserver.org/~acc/other/xff.html">XFF demo</a> for more details.</p>
            <h5>Forwarded IP addresses:</h5>    
            <table class="table table-condensed table-striped">
            {foreach $proxies as $proxy}
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

            	 <tr>
                    <td><span class="label {if $proxy.trust == false}label-important{/if}">{if $proxy.trust == false}un{/if}trusted</span>{if $origin == $proxy.ip}<span class="label label-inverse">origin</span>{/if}</td>
                    <td>{$proxy.ip}<br /><span class="muted">{if $proxy.rdns != NULL}RDNS: {$proxy.rdns}{elseif $proxy.routable == false}<em><a style="color:grey;" href="http://en.wikipedia.org/wiki/Private_network">Non-routable address</a></em>{elseif $proxy.rdnsfailed == true}<em>(unable to determine address)</em>{else}<em>(no rdns available)</em>{/if}</span></td>
                    <td>{if $proxy.trust == false && $proxy.routable == true && $proxy.rdnsfailed == false}<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User_talk:{$proxy.ip}">Talk page</a>
						<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$proxy.ip}">Local Contributions</a>
						<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=tparis-pcount&amp;data={$proxy.ip}">Deleted Edits</a>
						<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=luxo-contributions&amp;data={$proxy.ip}">Global Contributions</a>
						<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page={$proxy.ip}">Local Block Log</a>
						<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:BlockList/{$proxy.ip}">Active Local Blocks</a>
						<a class="btn btn-small" target="_blank" href="https://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page={$proxy.ip}">Global Block Log</a>
						<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:GlobalBlockList/{$proxy.ip}">Active Global Blocks</a>
						<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=oq-whois&amp;data={$proxy.ip}">Whois</a>
						<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=ipinfodb-locator&amp;data={$proxy.ip}">Geolocate</a>
						<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$proxy.ip}">Abuse Filter Log</a>
						{if $ischeckuser == true}<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$proxy.ip}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$id}">CheckUser</a>{/if}
					{/if}</td></tr>
            {/foreach}
            </table>
        </div>
        {else}
        	<div class="row-fluid">
        		<h4>IP Address links:</h4>
        		<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$ip}">Local Contributions</a>
				<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=tparis-pcount&amp;data={$ip}">Deleted Edits</a>
				<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=luxo-contributions&amp;data={$ip}">Global Contributions</a>
				<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page={$ip}">Local Block Log</a>
				<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:BlockList/{$ip}">Active Local Blocks</a>
				<a class="btn btn-small" target="_blank" href="https://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page={$ip}">Global Block Log</a>
				<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:GlobalBlockList/{$ip}">Active Global Blocks</a>
				<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=oq-whois&amp;data={$ip}">Whois</a>
				<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=ipinfodb-locator&amp;data={$ip}">Geolocate</a>
				<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$ip}">Abuse Filter Log</a>
				{if $ischeckuser == true}<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ip}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$id}">CheckUser</a>{/if}
			</div>{/if}
        <hr />{/if}
        <div class="row-fluid">
            <h4>Username data:</h4>
            
            {if $isblacklisted}<div class="alert">Requested username is blacklisted.</div>{/if}
           
            <div class="btn-group">
                <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User:{$usernamerawunicode|escape:'url'}">User page</a>
                <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page={$usernamerawunicode|escape:'url'}">Creation log</a>
                <a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=sulutil&amp;data={$usernamerawunicode|escape:'url'}">SUL</a>
                <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$usernamerawunicode|escape:'url'}">Special:CentralAuth</a>
                <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3AListUsers&amp;username={$usernamerawunicode|escape:'url'}&amp;group=&amp;limit=1">Username list</a>
                <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3ASearch&amp;profile=advanced&amp;search={$usernamerawunicode|escape:'url'}&amp;fulltext=Search&amp;ns0=1&amp;redirs=1&amp;profile=advanced">Wikipedia mainspace search</a>
                <a class="btn btn-small" target="_blank" href="https://www.google.com/search?q={$usernamerawunicode|escape:'url'}">Google search</a>
            </div>
            
            <h5>AntiSpoof results:</h5>
            {if !$spoofs}
            <p class="muted">None detected</p>
            {elseif !is_array($spoofs)}
            <div class="alert alert-error">{$spoofs}</div>
            {else}
            	<table class="table table-condensed table-striped">
            	{foreach $spoofs as $spoof}
            	{if $spoof == $username}<tr><td></td><td><h3>Note: This account has already been created</h3></td></tr>{continue}{/if}
            	<tr><td><a target="_blank" href="https://en.wikipedia.org/wiki/User:{$spoof|escape:'url'}">{$spoof}</a></td>
            	<td><a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$spoof|escape:'url'}">User page</a>
            	<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A{$spoof|escape:'url'}&amp;year=&amp;month=-1&amp;tagfilter=&amp;hide_patrol_log=1&amp;hide_review_log=1&amp;hide_thanks_log=1">Logs</a>
            	<a class="btn btn-small" target="_blank" href="http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&amp;showblocks=1&amp;username={$spoof|escape:'url'}">SUL</a>
            	<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$spoof|escape:'url'}">Special:CentralAuth</a>
            	<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}">Send Password reset</a>
            	<a class="btn btn-small" target="_blank" href="https://tools.wmflabs.org/xtools/pcount/index.php?lang=en&amp;wiki=wikipedia&amp;name={$spoof|escape:'url'}">Count</a></td>
              </tr>
            	{/foreach}
            	</table>
            {/if}
        </div>
        
        <div class="row-fluid container-fluid">
            <div class="row-fluid">
                <div class="span6">
                    <h4>Other requests from {if $showinfo == true}{$email}{else}this email address{/if}</h4>
                    {if $email == "acc@toolserver.org"}<p class="muted">Email information cleared</p>
                    {elseif $numemail == 0}<p class="muted">None detected</p>
                    {else}<table class="table table-condensed table-striped">
                    {foreach $otheremail as $others}
                    <tr><td>{$others.date}</td><td><a target="_blank" href="{$tsurl}/acc.php?action=zoom&amp;id={$others.id}">{$others.name}</a>
                  </td></tr>
                    {/foreach}
                    </table>
                    {/if}
                </div>
                <div class="span6">
                    <h4>Other requests from {if $showinfo == true}{$ip}{else}this IP address{/if}</h4>
                    {if $ip == "127.0.0.1"}<p class="muted">IP information cleared</p>
                    {elseif $numip == 0}<p class="muted">None detected</p>
                    {else}<table class="table table-condensed table-striped">
                    {foreach $otherip as $others}
                    <tr><td>{$others.date}</td><td><a target="_blank" href="{$tsurl}/acc.php?action=zoom&amp;id={$others.id}">{$others.name}</a></td></tr>
                    {/foreach}
                    </table>
                    {/if}
                </div>
            </div>
        </div>
        

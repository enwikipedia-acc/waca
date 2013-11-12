{if $proxyip != NULL}
  <div class="row-fluid">
    <h4>IP Address data:</h4>
    <p class="muted">
        This request came from {$ip}, stating it was forwarded for {$proxyip} via the X-Forwared-For HTTP header. 
        The IP address which Wikipedia will see is the first "untrusted" IP address in the list below. 
        Links are shown for all addresses starting from where the chain becomes untrusted. 
        IPs past the first untrusted address are not trusted to be correct. 
        Please see the <a href="https://toolserver.org/~acc/other/xff.html">XFF demo</a> for more details.
    </p>
    <h5>Forwarded IP addresses:</h5>    
    <table class="table table-condensed table-striped">
      {foreach $proxies as $proxy}
        <tr>
          <td><span class="label {if $proxy.trust == false}label-important{/if}">{if $proxy.trust == false}un{/if}trusted</span>{if $origin == $proxy.ip}<span class="label label-inverse">origin</span>{/if}</td>
          <td>
            {$proxy.ip}
            <br />
            <span class="muted">
              {if $proxy.rdns != NULL}
                RDNS: {$proxy.rdns}
              {elseif $proxy.routable == false}
                <em>
                  <a style="color:grey;" href="https://en.wikipedia.org/wiki/Private_network">Non-routable address</a>
                </em>
              {elseif $proxy.rdnsfailed == true}
                <em>(unable to determine address)</em>
              {else}
                <em>(no rdns available)</em>
              {/if}
            </span>
          </td>
          <td>
            {if $proxy.trust == false && $proxy.routable == true && $proxy.rdnsfailed == false}
              <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User_talk:{$proxy.ip}">Talk page</a>
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
						  {if $ischeckuser == true}
                <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$proxy.ip}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$id}">CheckUser</a>
              {/if}
					  {/if}
          </td>
        </tr>
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
		{if $ischeckuser == true}
      <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ip}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$id}">CheckUser</a>
    {/if}
	</div>
{/if}
<hr />

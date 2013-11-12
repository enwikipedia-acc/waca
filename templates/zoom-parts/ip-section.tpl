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
        {include file="zoom-parts/ip-links.tpl" ipaddress="{$proxy.ip}"}
      {/if}
          </td>
        </tr>
      {/foreach}
    </table>
  </div>
{else}
  <div class="row-fluid">
    <h4>IP Address links:</h4>
    {include file="zoom-parts/ip-links.tpl" ipaddress="{$ip}"}
  </div>
{/if}
<hr />

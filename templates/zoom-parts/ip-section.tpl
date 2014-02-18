<!-- tpl:zoom-parts/ip-section.tpl -->
{if $proxyip != NULL}
  <div class="row-fluid">
    <h3>IP Address data:</h3>
    <p class="muted">
        This request came from {$request->getIP()}, stating it was forwarded for {$request->getForwardedIp()} via the X-Forwarded-For HTTP header. 
        The IP address which Wikipedia will see is the first "untrusted" IP address in the list below. 
        Links are shown for all addresses starting from where the chain becomes untrusted. 
        IPs past the first untrusted address are not trusted to be correct. 
        Please see the <a href="https://accounts-dev.wmflabs.org/other/xff.html">XFF demo</a> for more details.
    </p>
    <h4>Forwarded IP addresses:</h4>    
    <table class="table table-condensed table-striped">
      {foreach $proxies as $proxy}
        <tr>
          <td>

            {if ! $proxy.trust}
            <span class="label label-important">untrusted</span>
            {/if}

            {if $origin == $proxy.ip}
              <span class="label label-inverse">origin</span>
            {else}
              {if $proxy.trust}
              <span class="label">trusted</span>
              {/if}
            {/if}

            {if $proxy.trustedlink & ! $proxy.trust}
            <span class="label label-warning">trusted link</span>
            {/if}
          </td>
          <td>
            {$proxy.ip}
            <br />
            <span class="muted">
              {if $proxy.routable == false}
                <em>
                  <a style="color:grey;" href="https://en.wikipedia.org/wiki/Private_network">Non-routable address</a>
                </em>
              {elseif $proxy.rdnsfailed == true}
                <em>(unable to determine address)</em>
              {elseif $proxy.rdns != NULL}
                RDNS: {$proxy.rdns}
              {else}
              <em>(no rdns available)</em>
              {/if}
            </span>
			<br />
			<span class="muted">
			{if $proxy.location != null}
				{$proxy.location.cityName}, {$proxy.location.regionName}, {$proxy.location.countryName}
			{else}
				<em>Location unavailable</em>
			{/if}
			</span>
          </td>
          <td>
            {if $proxy.showlinks}
              {include file="zoom-parts/ip-links.tpl" ipaddress="{$proxy.ip}" index="{$proxy@iteration}"}
            {/if}
          </td>
        </tr>
      {/foreach}
    </table>
  </div>
{else}
  <div class="row-fluid">
    <h3>IP Address links:</h3>
    {include file="zoom-parts/ip-links.tpl" ipaddress="{$request->getTrustedIp()}" index="0"}
  </div>
{/if}
<hr />
<!-- /tpl:zoom-parts/ip-section.tpl -->

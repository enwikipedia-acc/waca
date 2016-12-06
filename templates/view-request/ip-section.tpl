{if $requestHasForwardedIp}
    <div class="row-fluid">
        <h3>IP Address data:</h3>
        <p class="muted">
            This request came from {$requestRealIp}, stating it was forwarded for {$requestForwardedIp} via the
            X-Forwarded-For HTTP header. The IP address which Wikipedia will see is the first "untrusted" IP address in
            the list below. Links are shown for all addresses starting from where the chain becomes untrusted. IPs past
            the first untrusted address are not trusted to be correct. Please see the
            <a href="https://accounts-dev.wmflabs.org/other/xff.html">XFF demo</a> for more details.
        </p>
        <h4>Forwarded IP addresses:</h4>
        <table class="table table-condensed table-striped">
            {foreach $requestProxyData as $proxy}
                <tr>
                    <td>
                        {if ! $proxy.trust}
                            <span class="label label-important">untrusted</span>
                        {/if}

                        {if $forwardedOrigin == $proxy.ip}
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
                        {if $proxy.routable == false}
                            <em>
                                <a class="muted" href="https://en.wikipedia.org/wiki/Private_network">
                                    Non-routable address
                                </a>
                            </em>
                        {elseif $proxy.rdnsfailed == true}
                            <em class="muted">(unable to determine address)</em>
                        {elseif $proxy.rdns != NULL}
                            <span class="muted">RDNS: {$proxy.rdns}</span>
                        {else}
                            <em class="muted">(no rdns available)</em>
                        {/if}
                        <br />
                        {if $proxy.location != null}
                            <span class="muted">
                                {$proxy.location.cityName}, {$proxy.location.regionName}, {$proxy.location.countryName}
                            </span>
                        {else}
                            <em class="muted">Location unavailable</em>
                        {/if}
                    </td>
                    <td>
                        {if $proxy.showlinks}
                            {include file="view-request/ip-links.tpl" ipaddress="{$proxy.ip}" index="{$proxy@iteration}"}
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
{else}
    <div class="row-fluid">
        <h3>IP Address links:</h3>
        {include file="view-request/ip-links.tpl" ipaddress="{$requestTrustedIp}" index="0"}
    </div>
{/if}
<hr/>

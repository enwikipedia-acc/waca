{if $requestHasForwardedIp}
    <h3>IP Address data:</h3>
    <p class="text-muted">
        This request came from {$requestRealIp}, stating it was forwarded for {$requestForwardedIp} via the
        X-Forwarded-For HTTP header. The IP address which Wikipedia will see is the first "untrusted" IP address in
        the list below. Links are shown for all addresses starting from where the chain becomes untrusted. IPs past
        the first untrusted address are not trusted to be correct. Please see the
        <a href="{$baseurl}/internal.php/xffdemo">XFF demo</a> for more details.
    </p>
    <h5>Forwarded IP addresses:</h5>
    <table class="table table-sm table-striped">
        {foreach $requestProxyData as $proxy}
            <tr>
                <td>
                    {if ! $proxy.trust}
                        <span class="badge badge-danger">untrusted</span>
                    {/if}

                    {if $forwardedOrigin == $proxy.ip}
                        <span class="badge badge-dark">origin</span>
                    {else}
                        {if $proxy.trust}
                            <span class="badge badge-secondary">trusted</span>
                        {/if}
                    {/if}

                    {if $proxy.trustedlink & ! $proxy.trust}
                        <span class="badge badge-warning">trusted link</span>
                    {/if}
                </td>
                <td>
                    {$proxy.ip}
                    <br />
                    {if $proxy.routable == false}
                        <em>
                            <a class="text-muted" href="https://en.wikipedia.org/wiki/Private_network">
                                Non-routable address
                            </a>
                        </em>
                    {elseif $proxy.rdnsfailed == true}
                        <em class="text-muted">(unable to determine address)</em>
                    {elseif $proxy.rdns != NULL}
                        <span class="text-muted">RDNS: {$proxy.rdns}</span>
                    {else}
                        <em class="text-muted">(no rdns available)</em>
                    {/if}
                    <br />
                    {if $proxy.location != null}
                        <span class="text-muted">
                            {$proxy.location.cityName}, {$proxy.location.regionName}, {$proxy.location.countryName}
                        </span>
                    {else}
                        <em class="text-muted">Location unavailable</em>
                    {/if}
                </td>
                <td>
                    {if $proxy.showlinks}
                        {include file="view-request/ip-links.tpl" ipaddress="{$proxy.ip}" protocol="{$proxy.protocol}" index="{$proxy@iteration}"}
                    {/if}
                </td>
            </tr>
        {/foreach}
    </table>
{else}
    <h3>IP Address links:</h3>
    {include file="view-request/ip-links.tpl" ipaddress="{$requestTrustedIp}" protocol="{$requestTrustedIpProtocol}" index="0"}
{/if}

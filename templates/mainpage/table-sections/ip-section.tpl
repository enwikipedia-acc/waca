{if $list->requestTrustedIp[$r->getId()] === $list->dataClearIp}
    <span class="text-muted font-italic">IP address purged</span>
{else}
    <a href="https://en.wikipedia.org/wiki/User_talk:{$list->requestTrustedIp[$r->getId()]|escape}" target="_blank">{$list->requestTrustedIp[$r->getId()]|escape}</a>
    <span class="badge badge-pill {if $list->relatedIpRequests[$r->getId()] > 0}badge-danger{else}badge-secondary{/if}"
          data-toggle="tooltip" data-original-title="{$list->relatedIpRequests[$r->getId()]} other request(s) from this IP address"
    >
                            {$list->relatedIpRequests[$r->getId()]}
                        </span>
{/if}
{if $r->getEmail() === $list->dataClearEmail}
    <span class="text-muted font-italic">Email address purged</span>
{else}
    {$r->getEmail()|escape}
    <span class="badge badge-pill {if $list->relatedEmailRequests[$r->getId()] > 0}badge-danger{else}badge-secondary{/if}"
          data-toggle="tooltip" data-original-title="{$list->relatedEmailRequests[$r->getId()]} other request(s) from this email address"
    >
                            {$list->relatedEmailRequests[$r->getId()]}
                        </span>
{/if}
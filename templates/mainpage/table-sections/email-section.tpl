{if $r->getEmail() === $list->dataClearEmail}
    <span class="text-muted font-italic">Email address purged</span>
{else}
    {$r->getEmail()|escape}
    {if $list->relatedEmailRequests[$r->getId()] > 0}
        <span class="badge badge-pill badge-danger"
            data-toggle="tooltip" data-original-title="{$list->relatedEmailRequests[$r->getId()]} other request(s) from this email address"
        >
            <i class="fas fa-clone"></i>&nbsp;{$list->relatedEmailRequests[$r->getId()]}
        </span>
    {/if}
    {if !$list->commonEmail[$r->getId()]}<span class="badge badge-warning badge-pill" data-toggle="tooltip" title="Uncommon email domain"><i class="fas fa-gem"></i></span>{/if}
{/if}
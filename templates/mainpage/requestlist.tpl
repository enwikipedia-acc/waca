<!-- requestlist.tpl -->
{if $section.total > $requestLimitShowOnly}
    <div class="alert alert-warning alert-accordion">
        <strong>Miser mode:</strong>
        Not all requests are shown for speed. Only {$requestLimitShowOnly} of {$section.total} are shown here.
        <a class="btn btn-sm btn-outline-secondary" href="{$baseurl}/internal.php/requestList?queue={$section.api|escape:'url'}">
            Show all {$section.total} requests
        </a>
    </div>
{/if}
{if $section.total > 0}
    {include file="mainpage/requesttable.tpl" list=$section.requests sort=$defaultSort dir=$defaultSortDirection}
{else}
    <span class="font-italic text-muted">No requests at this time</span>
{/if}

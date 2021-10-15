<div class="card overflow-visible">
    <div class="card-header py-0">
        <div class="row">
            <div class="col position-relative">
                <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#requestListAccordion" data-target="#collapse{$section.api|escape}">
                    {if $section.special !== null}<span class="badge badge-secondary">{$section.special|escape}</span>{/if}
                    {$header|escape} <span class="badge {if $section.total > $requestLimitShowOnly}badge-danger{else}badge-info{/if} badge-pill">{if $section.total > 0}{$section.total}{/if}</span>
                </button>
            </div>
            {if $section.total > 0 && $section.showAll}
                <div class="col-auto">
                    <a href="{$baseurl}/internal.php/requestList?queue={$section.api|escape:'url'}" class="btn text-muted">Show all</a>
                </div>
            {/if}
        </div>
    </div>
    <div id="collapse{$section.api|escape}" class="collapse" data-parent="#requestListAccordion">
        <div class="card-body">
            {if $section.help !== null}
                <div class="alert alert-info alert-accordion prewrap">{$section.help|escape}</div>
            {/if}
            {include file="mainpage/requestlist.tpl" showStatus={$section.special !== null}}
        </div>
    </div>
</div>

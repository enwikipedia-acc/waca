<div class="accordion-group">
    <div class="accordion-heading">
        <a class="accordion-toggle" data-toggle="collapse" data-parent="#requestListAccordion"
           href="#collapse{$section.api}">
            {if $section.special !== null}<span class="label label-info">{$section.special|escape}</span>{/if}
            {$header|escape} <span class="badge {if $section.total > $requestLimitShowOnly}badge-important{else}badge-info{/if}">{if $section.total > 0}{$section.total}{/if}</span>
        </a>
    </div>
    <div id="collapse{$section.api|escape}" class="accordion-body collapse out">
        <div class="accordion-inner">
            {if $section.help !== null}<p class="muted">{$section.help|escape}</p>{/if}
            {include file="mainpage/requestlist.tpl" requests=$section showStatus={$section.special !== null}}
        </div>
    </div>
</div>
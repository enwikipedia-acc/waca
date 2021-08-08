<!-- tpl:pager.tpl -->
<nav aria-label="Page navigation" class="my-3">
    <ul class="pagination">
        <li class="page-item {if $pagedata.canprev}{else}disabled{/if}">
            <a class="page-link" href="{if $pagedata.canprev}?page=1&amp;{$searchParamsUrl}{else}#{/if}">&laquo;&nbsp;First</a>
        </li>
        <li class="page-item {if $pagedata.canprev}{else}disabled{/if}">
            <a class="page-link" href="{if $pagedata.canprev}?page={$page - 1}&amp;{$searchParamsUrl}{else}#{/if}">&lsaquo;&nbsp;Prev</a>
        </li>

        {foreach from=$pagedata.pages item=pagenum}
            <li {if $pagenum == $page}class="page-item active"{/if}>
                <a class="page-link" href="?page={$pagenum}&amp;{$searchParamsUrl}">{$pagenum}</a>
            </li>
        {/foreach}

        <li class="page-item {if $pagedata.cannext}{else}disabled{/if}">
            <a class="page-link" href="{if $pagedata.cannext}?page={$page + 1}&amp;{$searchParamsUrl}{else}#{/if}">Next&nbsp;&rsaquo;</a>
        </li>
        <li class="page-item {if $pagedata.cannext}{else}disabled{/if}">
            <a class="page-link" href="{if $pagedata.cannext}?page={$pagedata.maxpage}&amp;{$searchParamsUrl}{else}#{/if}">Last&nbsp;&raquo;</a>
        </li>
    </ul>
</nav>
<!-- /tpl:pager.tpl -->

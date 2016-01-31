{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header"><h1>{$statsPageTitle}</h1></div>
    <div class="row-fluid">
        <div class="span12">
            {block name="statisticsContent"}{$legacyContent}{/block}
        </div>
    </div>
{/block}
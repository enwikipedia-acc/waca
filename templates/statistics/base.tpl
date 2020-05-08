{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <h1>{$statsPageTitle}</h1>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-md-12">
            {block name="statisticsContent"}{$legacyContent}{/block}
        </div>
    </div>
{/block}

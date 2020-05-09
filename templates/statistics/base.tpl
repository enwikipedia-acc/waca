{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{$statsPageTitle}</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            {block name="statisticsContent"}{$legacyContent}{/block}
        </div>
    </div>
{/block}

{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Job Queue Management <small class="text-muted">Manage background jobs</small></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    {if $canSeeAll}
                        <a class="btn btn-sm btn-outline-secondary" href="{$baseurl}/internal.php/jobQueue/all"><i class="far fa-eye"></i>&nbsp;Show all</a>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <p class="muted">
        This page shows all of the background jobs which either are queued to run, or have failed and need manual
        intervention. To see all jobs regardless of status, please click 'Show All' above.
    </p>
    {include file="jobqueue/jobtable.tpl"}
{/block}

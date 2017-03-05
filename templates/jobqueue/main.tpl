{extends file="base.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Job Queue Management
            <small>Manage background jobs
                {if $canSeeAll}
                    <a class="btn btn-info" href="{$baseurl}/internal.php/jobQueue/all">
                        <i class="icon-white icon-eye-open"></i>&nbsp;Show all
                    </a>
                {/if}</small>
        </h1>
    </div>
    <p class="muted">
        This page shows all of the background jobs which either are queued to run, or have failed and need manual
        intervention. To see all jobs regardless of status, please click 'Show All' above.
    </p>
    {include file="jobqueue/jobtable.tpl"}
{/block}
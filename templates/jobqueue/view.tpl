{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Job Queue Management <small class="text-muted">Detail for Job #{$job->getId()}</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="row">
                <div class="col-12">
                    <h3>Job Detail</h3>
                </div>
            </div>
            <div class="row">
                <div class="">

                </div>
            </div>

            <div class="padded-data">
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Task</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">
                        {if isset($taskNameMap[$job->getTask()])}
                            {$taskNameMap[$job->getTask()]|escape}
                        {else}
                            {$job->getTask()|escape}
                        {/if}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Trigger user</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">
                        <a href="{$baseurl}/internal.php/statistics/users/detail?user={$job->getTriggerUserId()}">
                            {$user->getUsername()|escape}
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Request</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">
                        <a href="{$baseurl}/internal.php/viewRequest?id={$job->getRequest()|escape}">
                            #{$job->getRequest()|escape} ({$request->getName()|escape})
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Email Template</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">
                        {if $emailTemplate !== false}
                        <a href="{$baseurl}/internal.php/emailManagement/view?id={$job->getEmailTemplate()|escape}">
                            {$emailTemplate->getName()|escape}
                        </a>
                        {else}
                            <span class="muted">None.</span>
                        {/if}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Status</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">
                        <span rel="tooltip" title="{$statusDescriptionMap[$job->getStatus()]|escape}"
                              data-toggle="tooltip" id="#status{$job->getId()|escape}">{$job->getStatus()|escape}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Enqueue time</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">
                        <span rel="tooltip" title="{$job->getEnqueue()|relativedate}"
                              data-toggle="tooltip" id="#enqueue{$job->getId()|escape}">{$job->getEnqueue()|escape}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Parent task</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">
                        {if $parent === false}
                            <span class="muted">None.</span>
                        {else}
                            <a href="{$baseurl}/internal.php/jobQueue/view?id={$parent->getId()|escape}">{$taskNameMap[$parent->getTask()]|escape}</a>
                        {/if}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Error message</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">{$job->getError()|escape}</div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-lg-5 col-xl-4"><strong>Acknowledged</strong></div>
                    <div class="col-sm-9 col-lg-7 col-xl-8">
                        {if $job->getAcknowledged() === null}
                            N/A
                        {elseif $job->getAcknowledged() == 1}
                            Yes
                        {elseif $job->getAcknowledged() == 0}
                            No
                        {/if}
                    </div>
                </div>
            </div>

            <div class="row pb-2">
                {if $job->getStatus() == 'failed'}
                    <div class="col-md-6">
                        {if $canRequeue}
                            <form method="post" action="{$baseurl}/internal.php/jobQueue/requeue" class="form-inline">
                                <input type="hidden" name="updateVersion" value="{$job->getUpdateVersion()|escape}" />
                                <input type="hidden" name="job" value="{$job->getId()|escape}" />
                                {include file="security/csrf.tpl"}
                                <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-redo"></i>&nbsp;Requeue</button>
                            </form>
                        {/if}
                    </div>
                    <div class="col-md-6 pt-2 pt-md-0">
                        {if $job->getAcknowledged() == 0 && $canAcknowledge}
                            <form method="post" action="{$baseurl}/internal.php/jobQueue/acknowledge" class="form-inline">
                                <input type="hidden" name="updateVersion" value="{$job->getUpdateVersion()|escape}" />
                                <input type="hidden" name="job" value="{$job->getId()|escape}" />
                                {include file="security/csrf.tpl"}
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-thumbs-up"></i>&nbsp;Acknowledge</button>
                            </form>
                        {/if}
                    </div>
                {/if}
                {if $job->getStatus() == Waca\DataObjects\JobQueue::STATUS_QUEUED
                    || $job->getStatus() == Waca\DataObjects\JobQueue::STATUS_READY
                || $job->getStatus() == Waca\DataObjects\JobQueue::STATUS_WAITING}
                    <div class="col-md-6">
                        {if $canCancel}
                            <form method="post" action="{$baseurl}/internal.php/jobQueue/cancel" class="form-inline">
                                <input type="hidden" name="updateVersion" value="{$job->getUpdateVersion()|escape}" />
                                <input type="hidden" name="job" value="{$job->getId()|escape}" />
                                {include file="security/csrf.tpl"}
                                <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-stop-circle"></i>&nbsp;Cancel</button>
                            </form>
                        {/if}
                    </div>
                {/if}
            </div>
        </div>
        <div class="col-lg-8 pt-4 pt-lg-0">
            <h3>Job log</h3>
            {include file="logs/datatable.tpl" showComments=true logs=$log showUser=true showObject=false}

            {if isset($creationEmailText)}
                <h3>Parameters</h3>

                <h5>Email body</h5>
                <div class="prewrap">{$creationEmailText|escape}</div>
            {/if}
        </div>
    </div>
{/block}

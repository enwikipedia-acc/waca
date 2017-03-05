{extends file="base.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Job Queue Management
            <small>Detail for Job #{$job->getId()}</small>
        </h1>
    </div>

    <div class="row-fluid">
        <div class="span4">
            <h3>Job Detail</h3>
            <div class="container-fluid">
                <div class="row-fluid">
                    <div class="span6"><strong>Task</strong></div>
                    <div class="span6">
                        {if isset($taskNameMap[$job->getTask()])}
                            {$taskNameMap[$job->getTask()]|escape}
                        {else}
                            {$job->getTask()|escape}
                        {/if}
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span6"><strong>Trigger user</strong></div>
                    <div class="span6">
                        <a href="{$baseurl}/internal.php/statistics/users/detail?user={$job->getTriggerUserId()}">
                            {$user->getUsername()|escape}
                        </a>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span6"><strong>Request</strong></div>
                    <div class="span6">
                        <a href="{$baseurl}/internal.php/viewRequest?id={$job->getRequest()|escape}">
                            #{$job->getRequest()|escape} ({$request->getName()|escape})
                        </a>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span6"><strong>Email Template</strong></div>
                    <div class="span6">
                        {if $emailTemplate !== false}
                        <a href="{$baseurl}/internal.php/emailManagement/view?id={$job->getEmailTemplate()|escape}">
                            {$emailTemplate->getName()|escape}
                        </a>
                        {else}
                            <span class="muted">None.</span>
                        {/if}
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span6"><strong>Status</strong></div>
                    <div class="span6">
                        <a rel="tooltip" href="#status{$job->getId()|escape}" title="{$statusDescriptionMap[$job->getStatus()]|escape}"
                           data-toggle="tooltip" class="plainlinks"
                           id="#status{$job->getId()|escape}">{$job->getStatus()|escape}</a>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span6"><strong>Enqueue time</strong></div>
                    <div class="span6">
                        <a rel="tooltip" href="#enqueue{$job->getId()|escape}" title="{$job->getEnqueue()|relativedate}"
                           data-toggle="tooltip" class="plainlinks"
                           id="#enqueue{$job->getId()|escape}">{$job->getEnqueue()|escape}</a>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span6"><strong>Parent task</strong></div>
                    <div class="span6">
                        {if $parent === false}
                            <span class="muted">None.</span>
                        {else}
                            <a href="{$baseurl}/internal.php/jobQueue/view?id={$parent->getId()|escape}">{$taskNameMap[$parent->getTask()]|escape}</a>
                        {/if}
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span6"><strong>Error message</strong></div>
                    <div class="span6">{$job->getError()|escape}</div>
                </div>
                <div class="row-fluid">
                    <div class="span6"><strong>Acknowledged</strong></div>
                    <div class="span6">
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
            <div class="span12">
                {if $job->getStatus() == 'failed'}
                    {if $canRequeue}
                        <form method="post" action="{$baseurl}/internal.php/jobQueue/requeue" class="form-compact">
                            <input type="hidden" name="updateVersion" value="{$job->getUpdateVersion()|escape}" />
                            <input type="hidden" name="job" value="{$job->getId()|escape}" />
                            {include file="security/csrf.tpl"}
                            <button type="submit" class="btn btn-danger input-block-level span6" style="margin-left:0">
                                <i class="icon-white icon-retweet"></i>&nbsp;Requeue</button>
                        </form>
                    {/if}
                    {if $job->getAcknowledged() == 0 && $canAcknowledge}
                        <form method="post" action="{$baseurl}/internal.php/jobQueue/acknowledge" class="form-compact">
                            <input type="hidden" name="updateVersion" value="{$job->getUpdateVersion()|escape}" />
                            <input type="hidden" name="job" value="{$job->getId()|escape}" />
                            {include file="security/csrf.tpl"}
                            <button type="submit" class="btn btn-primary input-block-level span6">
                                <i class="icon-white icon-thumbs-up"></i>&nbsp;Acknowledge</button>
                        </form>
                    {/if}
                {/if}
            </div>
        </div>
        <div class="span8">
            <h3>Job log</h3>
            {include file="logs/datatable.tpl" showComments=true logs=$log showUser=true showObject=false}
        </div>
    </div>
{/block}
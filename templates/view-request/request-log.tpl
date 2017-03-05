<div style="float:right" class="">
    <a href="{$baseurl}/internal.php/jobQueue/all?filterRequest={$requestId|escape}" class="btn" style="margin: 15px 0;">
        <i class="icon-wrench"></i>&nbsp;View job list
    </a>
</div>
<h3>Log:</h3>
<div style="overflow-x: hidden; overflow-y: scroll; max-height: 450px;" id="requestLog">
    <form action="{$baseurl}/internal.php/viewRequest/comment" method="post">
        {include file="security/csrf.tpl"}
        <table class="table table-condensed table-striped table-bordered table-hover">
            <tbody>
            {if $requestLogs}
                {foreach from=$requestLogs item=zoomrow name=logloop}
                    <tr {if $zoomrow.security == "admin"}class="error"{/if}>
                        <td>
                            {if $zoomrow.userid != null}
                                <a href='{$baseurl}/internal.php/statistics/users/detail?user={$zoomrow.userid}'>{$zoomrow.user|escape}</a>
                            {else}
                                {$zoomrow.user}
                            {/if}

                            {if $zoomrow.security == "admin"}
                                <br/>
                                <span class="label label-important">
									<i class="icon-white icon-lock"></i>&nbsp;Restricted
								</span>
                            {/if}
                        </td>
                        <td>
                            {if $zoomrow.type == "log"}
                                <em class="muted">{$zoomrow.entry|escape}</em>
                                {if $zoomrow.comment != null}
                                    <br/>
                                    {$zoomrow.comment|escape}
                                {/if}
                            {elseif $zoomrow.type == "joblog"}
                                <em class="muted">{$zoomrow.entry|escape}</em>
                                <br />
                                <a href="{$baseurl}/internal.php/jobQueue/view?id={$zoomrow.jobId|escape}">Job #{$zoomrow.jobId|escape} ({$zoomrow.jobDesc|escape})</a>
                            {else}
                                {if $zoomrow.canedit == true}
                                    <a class="btn btn-mini" href="{$baseurl}/internal.php/editComment?id={$zoomrow.id}">
                                        <i class="icon icon-pencil"></i>
                                    </a>
                                    &nbsp;
                                {/if}
                                {$zoomrow.comment|escape}
                            {/if}
                        </td>
                        <td>
                            <a rel="tooltip" href="#log{$smarty.foreach.logloop.index}" title="{$zoomrow.time|date}"
                               data-toggle="tooltip" class="plainlinks"
                               id="#log{$smarty.foreach.logloop.index}">{$zoomrow.time|relativedate}</a>
                        </td>
                    </tr>
                {/foreach}
            {else}
                <tr>
                    <td></td>
                    <td>
                        <em>None.</em>
                    </td>
                    <td></td>
                </tr>
            {/if}
            <tr>
                <td>
                    <a href="{$baseurl}/internal.php/statistics/users/detail?user={$currentUser->getId()}">{$currentUser->getUsername()|escape}</a>
                </td>
                <td>
                    <input type="hidden" name="request" value="{$requestId}"/>
                    <input type="hidden" name="visibility" value="user"/>
                    <input class="span12 input-compact" type="text" placeholder="Quick comment" name="comment"/>
                </td>
                <td>
                    <button class="btn btn-primary" type="submit">Save</button>
                    <label style="display: inline;" title="Restrict visibility of this comment">
                    <span class="label label-important">
                        <input type="checkbox" name="adminOnly"/>
                        <i class="icon-white icon-lock"></i>
                    </span>
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
<div class="float-right">
    <a href="{$baseurl}/internal.php/jobQueue/all?filterRequest={$requestId|escape}" class="btn btn-sm btn-outline-secondary">
        <i class="icon-wrench"></i>&nbsp;View job list
    </a>
</div>
<h3>Log:</h3>
<div class="overflow-auto scroll-bottom" id="requestLog">
    <form action="{$baseurl}/internal.php/viewRequest/comment" method="post">
        {include file="security/csrf.tpl"}
        <table class="table table-sm table-striped table-bordered table-hover">
            <tbody>
            {if $requestLogs}
                {foreach from=$requestLogs item=zoomrow name=logloop}
                    <tr {if $zoomrow.security == "admin"}class="table-danger"{/if}>
                        <td class="text-nowrap">
                            {if $zoomrow.userid != null}
                                <a href='{$baseurl}/internal.php/statistics/users/detail?user={$zoomrow.userid}'>{$zoomrow.user|escape}</a>
                            {else}
                                {$zoomrow.user}
                            {/if}

                            {if $zoomrow.security == "admin"}
                                <br/>
                                <span class="badge badge-danger">
									<i class="fas fa-lock"></i>&nbsp;Restricted
								</span>
                            {/if}
                        </td>
                        <td>
                            {if $zoomrow.type == "log"}
                                <em class="text-muted">{$zoomrow.entry|escape}</em>
                                {if $zoomrow.comment != null}
                                    <br/>
                                    <div class="prewrap">{$zoomrow.comment|escape}</div>
                                {/if}
                            {elseif $zoomrow.type == "joblog"}
                                <em class="text-muted">{$zoomrow.entry|escape}</em>
                                <br />
                                <a href="{$baseurl}/internal.php/jobQueue/view?id={$zoomrow.jobId|escape}">Job #{$zoomrow.jobId|escape} ({$zoomrow.jobDesc|escape})</a>
                            {else}
                                {if $zoomrow.canedit == true}
                                    <a class="btn btn-outline-secondary btn-sm float-right p-0" href="{$baseurl}/internal.php/editComment?id={$zoomrow.id}" title="Edit comment" data-toggle="tooltip" data-placement="top" >
                                        <i class="fas fa-edit"></i>
                                    </a>
                                {/if}
                                <div class="prewrap">{$zoomrow.comment|escape}</div>
                            {/if}
                        </td>
                        <td class="text-nowrap">
                            <span title="{$zoomrow.time|date}" data-toggle="tooltip" data-placement="top"
                               id="#log{$smarty.foreach.logloop.index}">{$zoomrow.time|relativedate}</span>
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
                <td class="text-nowrap">
                    <a href="{$baseurl}/internal.php/statistics/users/detail?user={$currentUser->getId()}">{$currentUser->getUsername()|escape}</a>
                </td>
                <td>
                    <input type="hidden" name="request" value="{$requestId}"/>
                    <input type="hidden" name="visibility" value="user"/>
                    <label class="sr-only" for="quickCommentBox">Comment on request</label>
                    <input class="form-control" type="text" placeholder="Quick comment" name="comment" id="quickCommentBox"/>
                </td>
                <td class="text-nowrap">
                    <button class="btn btn-primary" type="submit">Save</button>
                    <label for="adminOnly" class="sr-only">Restrict visibility of this comment</label>
                    <span class="badge badge-danger" data-toggle="tooltip" data-placement="top" title="Restrict visibility of this comment">
                        <input type="checkbox" name="adminOnly" id="adminOnly" />
                        <i class="fas fa-lock"></i>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>

<div class="float-right">
    <a href="{$baseurl}/internal.php/jobQueue/all?filterRequest={$requestId|escape}" class="btn btn-sm btn-outline-secondary">
        <i class="icon-wrench"></i>&nbsp;View job list
    </a>
</div>
<h3>Log:</h3>
<div class="overflow-auto scroll-bottom" id="requestLog">
    <table class="table table-sm table-striped table-bordered table-hover">
        <tbody>
        {if $requestLogs}
            {foreach from=$requestLogs item=zoomrow name=logloop}
                    <tr class="{if $zoomrow.security == "admin"}table-danger{elseif $zoomrow.security == "checkuser"}table-visited{/if}">
                    <td class="text-nowrap">
                        {if $zoomrow.userid != null}
                            <a href='{$baseurl}/internal.php/statistics/users/detail?user={$zoomrow.userid}'>{$zoomrow.user|escape}</a>
                        {else}
                            {$zoomrow.user}
                        {/if}

                        {if $zoomrow.security !== "user"}
                            <br/>
                            {if $zoomrow.security === "admin"}
                            <span class="badge badge-danger">
                                <i class="fas fa-lock"></i>&nbsp;Restricted
                            </span>
                            {/if}
                            {if $zoomrow.security === "requester"}
                            <span class="badge badge-info">
                                <i class="fas fa-user"></i>&nbsp;Requester
                            </span>
                            {/if}
                            {if $zoomrow.security === "checkuser"}
                            <span class="badge badge-visited">
                                <i class="fas fa-lock"></i>&nbsp;CheckUser only
                            </span>
                            {/if}
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
                            {if $zoomrow.canflag}
                                <form action="{$baseurl}/internal.php/flagComment" method="post">
                                    {include file="security/csrf.tpl"}
                                    <input type="hidden" name="comment" value="{$zoomrow.id}" />
                                    <input type="hidden" name="updateversion" value="{$zoomrow.updateversion}" />
                                    <input type="hidden" name="flag" value="{if $zoomrow.flagged}0{else}1{/if}" />
                                    <button class="btn {if $zoomrow.flagged}btn-danger{else}btn-outline-secondary{/if} btn-sm float-right p-0 ml-1" title="Flag comment for tool admin attention" data-toggle="tooltip" data-placement="top" type="submit">
                                        <i class="{if $zoomrow.flagged}fas{else}far{/if} fa-flag"></i>
                                    </button>
                                </form>
                            {else}
                                <div class="btn {if $zoomrow.flagged}btn-danger{else}btn-outline-secondary{/if} btn-sm float-right p-0 ml-1" title="Comment is {if !$zoomrow.flagged}not {/if}flagged tool admin attention" data-toggle="tooltip" data-placement="top">
                                    <i class="{if $zoomrow.flagged}fas{else}far{/if} fa-flag"></i>
                                </div>
                            {/if}

                            {if $zoomrow.canedit == true}
                                <a class="btn btn-outline-secondary btn-sm float-right p-0 ml-1" href="{$baseurl}/internal.php/editComment?id={$zoomrow.id}" title="Edit comment" data-toggle="tooltip" data-placement="top" >
                                    <i class="fas fa-edit"></i>
                                </a>
                            {/if}

                            <div class="prewrap">{$zoomrow.comment|escape}</div>
                        {/if}
                    </td>
                    <td class="text-nowrap">
                        <span title="{$zoomrow.time|date}" data-toggle="tooltip" data-placement="top"
                           id="#log{$smarty.foreach.logloop.index}">{$zoomrow.time|relativedate}</span>
                        {if $zoomrow.edited !== null}
                            <span class="small" data-toggle="tooltip" data-placement="left" title="Edited {$zoomrow.edited|relativedate} at {$zoomrow.edited|date}">
                                <i class="fas fa-star-of-life fa-w-16"></i></span>
                            {*<span class="badge badge-pill badge-dark px-0" data-toggle="tooltip" data-placement="left" title="Edited "></span>*}
                        {/if}
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
        </tbody>
        <tfoot>
            <form action="{$baseurl}/internal.php/viewRequest/comment" method="post">
                {include file="security/csrf.tpl"}
                <tr>
                    <td class="text-nowrap">
                        <a href="{$baseurl}/internal.php/statistics/users/detail?user={$currentUser->getId()}">{$currentUser->getUsername()|escape}</a>
                    </td>
                    <td>
                        <input type="hidden" name="request" value="{$requestId}"/>
                        <label class="sr-only" for="quickCommentBox">Comment on request</label>
                        <input class="form-control" type="text" placeholder="Quick comment" name="comment" id="quickCommentBox"/>
                    </td>
                    <td class="text-nowrap">
                    <div class="btn-group col" role="group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown"title="Restrict comment visibility" id="commentVisibilityButton">
                            <i id="commentVisibilityIcon" class="fas fa-lock-open"></i>
                        </button>
                        <ul class="dropdown-menu" id="commentVisibilityDropdown">
                            <h6 class="dropdown-header">Restrict comment visibility</h6>
                            <li class="dropdown-item">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="visibilityUser" name="visibility" class="custom-control-input" checked="checked" value="user">
                                    <label class="custom-control-label" for="visibilityUser">All tool users</label>
                                </div>
                            </li>
                            <li class="dropdown-item">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="visibilityAdmin" name="visibility" class="custom-control-input" value="admin">
                                    <label class="custom-control-label" for="visibilityAdmin">Tool admins and Checkusers</label>
                                </div>
                            </li>
                            <li class="dropdown-item">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="visibilityCU" name="visibility" class="custom-control-input" value="checkuser">
                                    <label class="custom-control-label" for="visibilityCU">Checkusers only</label>
                                </div>
                            </li>
                        </ul>

                        <button class="btn btn-primary col" type="submit" id="commentSaveButton">Save</button>
                    </div>


                    </td>
                </tr>
            </form>
        </tfoot>
    </table>
</div>

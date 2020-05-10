<h3>Log:</h3>
<div style="overflow-x: hidden; overflow-y: scroll; max-height: 450px;">
    <form action="{$baseurl}/internal.php/viewRequest/comment" method="post">
        {include file="security/csrf.tpl"}
        <table class="table table-sm table-striped table-bordered table-hover">
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
									<i class="fas fa-lock"></i>&nbsp;Restricted
								</span>
                            {/if}
                        </td>
                        <td>
                            {if $zoomrow.type == "log"}
                                <em class="text-muted">{$zoomrow.entry|escape}</em>
                                {if $zoomrow.comment != null}
                                    <br/>
                                    {$zoomrow.comment|escape}
                                {/if}
                            {else}
                                {if $zoomrow.canedit == true}
                                    <a class="btn btn-sm" href="{$baseurl}/internal.php/editComment?id={$zoomrow.id}">
                                        <i class="fas fa-edit"></i>
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
                    <input class="col-md-12" type="text" placeholder="Quick comment" name="comment"/>
                </td>
                <td>
                    <button class="btn btn-primary" type="submit">Save</button>
                    <label for= title="Restrict visibility of this comment">
                    <span class="label label-important">
                        <input type="checkbox" name="adminOnly"/>
                        <i class="fas fa-lock"></i>
                    </span>
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>

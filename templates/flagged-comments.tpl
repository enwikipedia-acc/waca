{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Flagged comments</h1>
            </div>
            <p>This page lists all comments which have been flagged for review. Please check each carefully and edit out any private data as required.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <table class="table table-striped table-sm sortable">
                <thead>
                    <tr>
                        <th class="text-nowrap">Comment ID</th>
                        <th>Visibility</th>
                        <th>User</th>
                        <th>Request</th>
                        <th>Date</th>
                        <th>Text</th>
                        <th data-defaultsort="disabled"></th>
                    </tr>
                </thead>
                <tbody>
                {foreach from=$comments item=data}
                    <tr>
                        {if $data.hidden}
                            <td class="text-muted"><del>(redacted)</del></td>
                        {else}
                            <td>{$data.id}</td>
                        {/if}
                        <td>
                            {if $data.visibility == 'requester'}
                                <span class="badge badge-info">
                                    <i class="fas fa-user"></i>&nbsp;Requester
                                </span>
                            {elseif $data.visibility == 'user'}
                                <span class="badge badge-secondary">
                                    <i class="fas fa-lock-open"></i>&nbsp;All users
                                </span>
                            {elseif $data.visibility == 'admin'}
                                <span class="badge badge-danger">
                                    <i class="fas fa-lock"></i>&nbsp;Restricted
                                </span>
                            {elseif $data.visibility == 'checkuser'}
                                <span class="badge badge-visited">
                                    <i class="fas fa-lock"></i>&nbsp;CheckUser only
                                </span>
                            {else}
                                {$data.visibility|escape}
                            {/if}
                        </td>
                        {if $data.hidden}
                            <td class="text-muted"><del>(redacted)</del></td>
                            <td class="text-muted"><del>(redacted)</del></td>
                            <td class="text-muted"><del>(redacted)</del></td>
                            <td class="text-muted"><del>(redacted)</del></td>
                            <td></td>
                        {else}
                            <td>{if $data.userid !== null}<a href="{$baseurl}/internal.php/statistics/users/detail?user={$data.userid|escape}">{$data.user|escape}</a>{/if}</td>
                            <td><a href="{$baseurl}/internal.php/viewRequest?id={$data.requestid|escape}">{$data.request|escape}</a></td>
                            <td data-value="{$data.time|date}" data-dateformat="YYYY-MM-DD hh:mm:ss" class="text-nowrap">
                                {$data.time|date}&nbsp;<span class="text-muted">{$data.time|relativedate}</span>
                            </td>
                            <td>{$data.comment|escape}</td>
                            <td class="table-button-cell text-right">
                                {if $editComments && ($editOthersComments || $data.userid == $currentUser->getId())}
                                    <a href="{$baseurl}/internal.php/editComment?id={$data.id}" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i>&nbsp;Edit</a>
                                {/if}
                                {if $canUnflag}
                                <form action="{$baseurl}/internal.php/flagComment" method="post" class="d-inline-block">
                                    {include file="security/csrf.tpl"}
                                    <input type="hidden" name="comment" value="{$data.id}" />
                                    <input type="hidden" name="updateversion" value="{$data.updateversion}" />
                                    <input type="hidden" name="flag" value="0" />
                                    <input type="hidden" name="return" value="list" />
                                    <button class="btn btn-outline-success btn-sm" type="submit">
                                        <i class="fas fa-flag"></i>&nbsp;Unflag
                                    </button>
                                </form>
                                {/if}
                            </td>
                        {/if}
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>

{/block}

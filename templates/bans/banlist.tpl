{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Ban Management <small class="text-muted">View, ban, and unban requesters</small></h1>

                <div class="btn-toolbar mb-2 mb-md-0">
                    {if $canSet}
                        <a class="btn btn-sm btn-outline-success" href="{$baseurl}/internal.php/bans/set"><i class="fas fa-plus"></i>&nbsp;Add new Ban</a>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h3>Active Ban List</h3>
            <table class="table table-striped sortable">
                <thead>
                <th>Target</th>
                <th>Banned by</th>
                <th>Reason</th>
                <th data-defaultsort="desc">Time</th>
                <th>Expiry</th>
                {if $canRemove}
                    <th data-defaultsort="disabled">Unban</th>
                {/if}
                </thead>
                <tbody>
                {foreach from=$activebans item="ban"}
                    <tr>
                        <td>
                            {include file="bans/bantarget.tpl"}
                        </td>
                        <td class="text-nowrap">{$usernames[$ban->getUser()]|escape}</td>
                        <td>{$ban->getReason()|escape}</td>
                        <td class="text-nowrap">{$ban->getDate()} <span class="text-muted">({$ban->getDate()|relativedate})</span></td>
                        <td class="text-nowrap">{if $ban->getDuration() === null}Indefinite{else}{date("Y-m-d H:i:s", $ban->getDuration())}{/if}</td>

                        {if $canRemove}
                            <td class="table-button-cell">
                                {if $banHelper->canUnban($ban)}
                                    <a class="btn btn-success btn-sm" href="{$baseurl}/internal.php/bans/remove?id={$ban->getId()}">
                                        <i class="fas fa-check-circle"></i><span class="d-none d-lg-inline">&nbsp;Unban</span>
                                    </a>
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

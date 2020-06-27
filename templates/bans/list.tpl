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
        <tr {if !$banHelper->isActive($ban)}class="text-muted"{/if}>
            <td>
                {include file="bans/bantarget.tpl"}
            </td>
            <td class="text-nowrap">{$usernames[$ban->getUser()]|escape}</td>
            <td>{$ban->getReason()|escape}</td>
            <td class="text-nowrap">{$ban->getDate()} <span class="text-muted">({$ban->getDate()|relativedate})</span></td>
            <td class="text-nowrap">
                {if $ban->getDuration() === null}Indefinite{else}{date("Y-m-d H:i:s", $ban->getDuration())}{/if}
                {if $ban->isActive() === false}<span class="badge badge-info">Unbanned</span>{/if}
                {if $ban->getDuration() < time() && $ban->getDuration() !== null}<span class="badge badge-warning">Expired</span>{/if}
            </td>

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
<table class="table table-striped sortable">
    <thead>
    <th>Target</th>
    <th>Banned by</th>
    <th>Action</th>
    <th>Reason</th>
    <th>Domain</th>
    <th data-defaultsort="desc">Time</th>
    <th>Expiry</th>
    {if $canRemove}
        <th data-defaultsort="disabled"></th>
    {/if}
    </thead>
    <tbody>
    {foreach from=$activebans item="ban"}
        <tr {if !$banHelper->isActive($ban)}class="text-muted"{/if}>
            <td>
                {include file="bans/bantarget.tpl"}
            </td>
            <td class="text-nowrap">{$usernames[$ban->getUser()]|escape}</td>
            <td>
                {if $ban->getAction() == $ban::ACTION_BLOCK}<abbr title="Blocks the user from submitting the request" data-toggle="tooltip">Block</abbr>{/if}
                {if $ban->getAction() == $ban::ACTION_DROP}<abbr title="Accepts the request for processing, but immediately drops it." data-toggle="tooltip">Drop</abbr>{/if}
                {if $ban->getAction() == $ban::ACTION_DEFER}<abbr title="Defers the request into the specified queue" data-toggle="tooltip">Defer</abbr> to {$ban->getTargetQueueObject()->getDisplayName()|escape}{/if}
                {if $ban->getAction() == $ban::ACTION_NONE}<abbr title="Does nothing but flag the request." data-toggle="tooltip">Report only</abbr>{/if}
            </td>
            <td>{include file="bans/banreason.tpl"}</td>
            <td class="text-nowrap">
                {if $ban->getDomain() === null}
                    <span class="badge badge-secondary"><i class="fas fa-globe-europe"></i>Global</span>
                {else}
                    {$domains[$ban->getDomain()]->getShortName()}
                {/if}
            </td>
            <td class="text-nowrap">{$ban->getDate()} <span class="text-muted">({$ban->getDate()|relativedate})</span></td>
            <td class="text-nowrap">
                {if $ban->getDuration() === null}Indefinite{else}{date("Y-m-d H:i:s", $ban->getDuration())}{/if}
                {if $ban->isActive() === false}<span class="badge badge-info">Unbanned</span>{/if}
                {if $ban->getDuration() < $currentUnixTime && $ban->getDuration() !== null}<span class="badge badge-warning">Expired</span>{/if}
            </td>

            {if $canRemove}
                <td class="table-button-cell">
                    {if $banHelper->canUnban($ban)}
                        <a class="btn btn-secondary btn-sm" href="{$baseurl}/internal.php/bans/replace?id={$ban->getId()}">
                            <i class="fas fa-pencil"></i><span class="d-none d-lg-inline">&nbsp;Edit</span>
                        </a>
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
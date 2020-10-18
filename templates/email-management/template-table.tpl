<table class="table table-striped">
    {foreach $templates as $row}
        <tr>
            <th>{$row->getName()|escape}</th>
            <td class="text-nowrap">
                {if $row->getDefaultAction() === Waca\DataObjects\EmailTemplate::ACTION_CREATED}
                    <span class="badge badge-success">Create</span>
                {elseif $row->getDefaultAction() === Waca\DataObjects\EmailTemplate::ACTION_NOT_CREATED}
                    <span class="badge badge-danger">Decline</span>
                {elseif $row->getDefaultAction() == null}
                    <span class="badge badge-secondary">No default</span>
                {else}
                    <span class="badge badge-info">Defer to {$row->getDefaultAction()|escape}</span>
                {/if}

                {if $row->getPreloadOnly()}<span class="d-md-none"><br /><span class="badge badge-info">Preload only</span></span>{/if}
            </td>
            <td class="text-nowrap d-none d-md-block">
                {if $row->getPreloadOnly()}<span class="badge badge-info">Preload only</span>{/if}
            </td>
            <td class="table-button-cell">
                {if $canEdit}
                    <a class="btn btn-outline-primary btn-sm" href="{$baseurl}/internal.php/emailManagement/edit?id={$row->getId()}">
                        <i class="fas fa-pencil-alt"></i>&nbsp;Edit
                    </a>
                {/if}
                <a class="btn btn-secondary btn-sm" href="{$baseurl}/internal.php/emailManagement/view?id={$row->getId()}">
                    <i class="fas fa-eye"></i>&nbsp;View
                </a>
            </td>
        </tr>
    {/foreach}
</table>

<table class="table table-striped">
    {foreach $templates as $row}
        <tr>
            <td>{$row@iteration}.</td>
            <th>{$row->getName()|escape}</th>
            <td>
                {if $row->getDefaultAction() === 'created'}
                    <span class="badge badge-success">Create</span>
                {elseif $row->getDefaultAction() === 'not created'}
                    <span class="badge badge-danger">Decline</span>
                {elseif $row->getDefaultAction() == null}
                    <span class="badge">No default</span>
                {else}
                    <span class="badge badge-info">Defer to {$row->getDefaultAction()|escape}</span>
                {/if}
            </td>
            <td>
                {if $row->getPreloadOnly()}<span class="badge badge-info">Preload only</span>{/if}
            </td>
            <td>
                {if $canEdit}
                    <a class="btn btn-warning" href="{$baseurl}/internal.php/emailManagement/edit?id={$row->getId()}">
                        <i class="fas fa-pencil-alt"></i>&nbsp;Edit Message
                    </a>
                {else}
                    <a class="btn btn-primary" href="{$baseurl}/internal.php/emailManagement/view?id={$row->getId()}">
                        <i class="fas fa-eye"></i>&nbsp;View Message
                    </a>
                {/if}
            </td>
        </tr>
    {/foreach}
</table>

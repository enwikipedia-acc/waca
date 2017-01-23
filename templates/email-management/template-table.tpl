<table class="table table-striped table-nonfluid">
    {foreach $templates as $row}
        <tr>
            <td>{$row@iteration}.</td>
            <th>{$row->getName()|escape}</th>
            <td>
                {if $row->getDefaultAction() === 'created'}
                    <span class="label label-success">Create</span>
                {elseif $row->getDefaultAction() === 'not created'}
                    <span class="label label-important">Decline</span>
                {elseif $row->getDefaultAction() == null}
                    <span class="label">No default</span>
                {else}
                    <span class="label label-info">Defer to {$row->getDefaultAction()|escape}</span>
                {/if}
            </td>
            <td>
                {if $row->getPreloadOnly()}<span class="label label-info">Preload only</span>{/if}
            </td>
            <td>
                {if $canEdit}
                    <a class="btn btn-warning" href="{$baseurl}/internal.php/emailManagement/edit?id={$row->getId()}">
                        <i class="icon-white icon-pencil"></i>&nbsp;Edit Message
                    </a>
                {else}
                    <a class="btn" href="{$baseurl}/internal.php/emailManagement/view?id={$row->getId()}">
                        <i class="icon-black icon-eye-open"></i>&nbsp;View Message
                    </a>
                {/if}
            </td>
        </tr>
    {/foreach}
</table>

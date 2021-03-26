<table class="table table-striped table-hover table-sm table-nonfluid">
    <thead>
    <tr>
        <th>Timestamp</th>
        {if $showUser}
            <th>User</th>
        {/if}
        <th>Action</th>
        {if $showObject}
            <th>Object</th>
        {/if}
        {if $showComments}
            <th>Comment</th>
        {/if}
    </tr>
    </thead>
    <tbody>
    {foreach from=$logs item=entry name=logloop}
        <tr>
            <td>{$entry.timestamp|date} <em class="text-muted">({$entry.timestamp|relativedate})</em></td>
            {if $showUser}
                <td>
                    {if $entry.userid != -1}
                        <a href='{$baseurl}/internal.php/statistics/users/detail?user={$entry.userid|escape}'>
                            {$entry.username|escape}
                        </a>
                    {else}
                        {$entry.username|escape}
                    {/if}
                </td>
            {/if}
            <td>{$entry.description|escape}</td>
            {if $showObject}
                <td>{$entry.objectdescription}</td>
            {/if}
            {if $showComments}
                <td>{$entry.comment}</td>
            {/if}
        </tr>
    {foreachelse}
    <tr>
        <td colspan="2"><span class="text-muted font-italic">No log entries</span></td>
    </tr>
    {/foreach}
    </tbody>
</table>

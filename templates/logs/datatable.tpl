<table class="table table-striped table-hover table-condensed table-nonfluid">
    <thead>
    <tr>
        <th>Timestamp</th>
        <th>User</th>
        <th>Action</th>
        <th>Object</th>
        {if $showComments}
            <th>Comment</th>
        {/if}
    </tr>
    </thead>
    <tbody>
    {foreach from=$logs item=entry name=logloop}
        <tr>
            <td>{$entry.timestamp|date} <em class="muted">({$entry.timestamp|relativedate})</em></td>
            <td>
                {if $entry.userid != -1}
                    <a href='{$baseurl}/internal.php/statistics/users/detail?user={$entry.userid|escape}'>
                        {$entry.username|escape}
                    </a>
                {else}
                    {$entry.username|escape}
                {/if}
            </td>
            <td>{$entry.description|escape}</td>
            <td>{$entry.objectdescription}</td>
            {if $showComments}
                <td>{$entry.comment}</td>
            {/if}
        </tr>
    {/foreach}
    </tbody>
</table>

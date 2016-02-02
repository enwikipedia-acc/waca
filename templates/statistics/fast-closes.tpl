{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <table class="table table-striped table-hover table-condensed">
        <thead>
        <tr>
            <th>Request</th>
            <th>User</th>
            <th>Time Taken</th>
            <th>Close Type</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$dataTable item=row}
            <tr>
                <td><a href="{$baseurl}/internal.php/viewRequest?id={$row.request}">{$row.request}</a></td>
                <td><a href="{$baseurl}/internal.php/statistics/users/detail?user={$row.userid}">{$row.user|escape}</a></td>
                <td>{$row.timetaken|escape}</td>
                <td>{$row.closetype|escape}</td>
                <td>{$row.date|escape} <span class="muted">({$row.date|relativedate})</span></td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}
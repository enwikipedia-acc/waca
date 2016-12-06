{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <table class="table table-striped table-hover table-condensed">
        <thead>
        <tr>
            <th>Request</th>
            <th>Status</th>
            <th>User</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$dataTable item=row}
            <tr>
                <td><a href="{$baseurl}/internal.php/viewRequest?id={$row.requestid}">{$row.name|escape}</a></td>
                <td>{$row.status|escape}</td>
                <td><a href="{$baseurl}/internal.php/statistics/users/detail?user={$row.userid}">{$row.user|escape}</a></td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}
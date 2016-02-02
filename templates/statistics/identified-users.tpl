{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <table class="table table-striped table-hover table-condensed">
        <thead>
        <tr>
            <th>Username</th>
            <th>Access Level</th>
            <th>Checkuser</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$dataTable item=row}
            <tr>
                <td><a href="{$baseurl}/internal.php/statistics/users/detail?user={$row.id}">{$row.username|escape}</a></td>
                <td>{$row.status|escape}</td>
                <td>{$row.checkuser|escape}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}
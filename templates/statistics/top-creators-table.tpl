<table class="table table-hover table-sm table-striped">
    <thead>
    <tr>
        <th>Position #</th>
        <th>Closed as created</th>
        <th>Username</th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$dataTable item="row" name="topcreators"}
        <tr {if $row.username == $currentUser->getUsername()}class="table-info"{/if}>
            <td>{$smarty.foreach.topcreators.iteration}</td>
            <td>{$row.count}</td>
            <td>
                <a href="{$baseurl}/internal.php/statistics/users/detail?user={$row.userid|escape}" {if $row.status == 'Deactivated'}class="text-muted"{/if}> {$row.username|escape}</a>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>

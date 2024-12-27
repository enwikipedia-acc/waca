<div class="col-md-6">
    <h4>Statistics</h4>
    <table class="table table-striped table-sm">

        {foreach from=$requestCountData key=header item=count}
            <tr>
                <th>{$header|escape}</th>
                <td>{$count|escape}</td>
            </tr>
        {/foreach}

        <tr>
            <th>Unconfirmed requests</th>
            <td>{$statsUnconfirmed|escape}</td>
        </tr>
        <tr>
            <th>Tool administrators</th>
            <td>{$statsAdminUsers|escape}</td>
        </tr>
        <tr>
            <th>Tool users</th>
            <td>{$statsUsers|escape}</td>
        </tr>
        <tr>
            <th>Tool deactivated users</th>
            <td>{$statsDeactivatedUsers|escape}</td>
        </tr>
        <tr>
            <th>New tool users</th>
            <td>{$statsNewUsers|escape}</td>
        </tr>
        <tr>
            <th>Request with most comments</th>
            <td>
                <a href="{$baseurl}/internal.php/viewRequest?id={$mostComments|escape}">{$mostComments|escape}</a>
            </td>
        </tr>
    </table>
</div>

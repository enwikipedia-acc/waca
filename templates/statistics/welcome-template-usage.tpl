{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <table class="table table-striped table-hover table-sm table-nonfluid">
        <thead>
        <tr><th rowspan="2">Template</th><th colspan="2">Users using this template</th></tr>
        <tr>
            <th>Active users</th>
            <th>All users</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$dataTable item=row}
            <tr>
                <td><a href="{$baseurl}/internal.php/welcomeTemplates/view?template={$row.templateid}">{$row.usercode|escape}</a></td>
                <td class="text-right">{$row.activecount|escape}</td>
                <td class="text-right">{$row.usercount|escape}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}

{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <table class="table table-striped table-hover table-condensed">
        <thead>
        <tr>
            <th>Template</th>
            <th>Active users using this template</th>
            <th>All users using this template</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$dataTable item=row}
            <tr>
                <td><a href="{$baseurl}/internal.php/welcomeTemplates/view?template={$row.templateid}">{$row.usercode|escape}</a></td>
                <td>{$row.activecount|escape}</td>
                <td>{$row.usercount|escape}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}
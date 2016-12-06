{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <table class="table table-striped table-hover table-condensed table-nonfluid">
        <thead>
        <tr>
            <th>Year</th>
            <th>Month</th>
            <th>Requests Closed</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$dataTable item=row}
            <tr>
                <td>{$row.year|escape}</td>
                <td>{$row.month|escape}</td>
                <td>{$row.closed|escape}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}
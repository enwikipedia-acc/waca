{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <table class="table table-striped table-hover table-sm table-nonfluid sortable">
        <thead>
            <tr>
                <th class="px-3" colspan="2" data-defaultsort="disabled"></th>
                <th class="border-left px-3" colspan="2" data-defaultsort="disabled">Requests Submitted</th>
                <th class="border-left px-3" colspan="2" data-defaultsort="disabled">Requests Closed</th>
                <th class="border-left px-3" colspan="1" data-defaultsort="disabled"></th>
                <th class="border-left px-3" colspan="2" data-defaultsort="disabled"><abbr title="The number of users who made a logged action this month">Active users</abbr></th>
                <th class="border-left px-3" colspan="6" data-defaultsort="disabled">Average request first-response times</th>
            </tr>
            <tr>
                <th class="px-3" data-defaultsort="desc">Year</th>
                <th class="px-3" data-defaultsort="disabled">Month</th>
                <th class="border-left px-3">#</th>
                <th class="px-3"><abbr title="Difference compared to previous month">&Delta;</abbr></th>
                <th class="border-left px-3">#</th>
                <th class="px-3"><abbr title="Difference compared to previous month">&Delta;</abbr></th>
                <th class="border-left px-3">&Delta; Open requests</th>
                <th class="border-left px-3">#</th>
                <th class="px-3"><abbr title="Difference compared to previous month">&Delta;</abbr></th>

                <th class="border-left px-3"><abbr title="Requests which were not deferred to a different queue during handling">Non-deferred</abbr></th>
                <th class="px-3"><abbr title="Difference compared to previous month">&Delta;</abbr></th>
                <th class="px-3"><abbr title="Standard deviation; an indication of how spread out the values are">&sigma;</abbr></th>
                <th class="border-left px-3"><abbr title="Requests which were deferred to a different queue during handling">Deferred</abbr></th>
                <th class="px-3"><abbr title="Difference compared to previous month">&Delta;</abbr></th>
                <th class="px-3"><abbr title="Standard deviation; an indication of how spread out the values are">&sigma;</abbr></th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$dataTable item=row}
            <tr>
                <td class="px-3" data-value="{$row.sortkey|escape}">{$row.year|escape}</td>
                <td class="px-3">{$row.month|escape}</td>

                <td class="px-3 border-left numeric" data-value="{$row.submitted|escape}">{$row.submitted|escape}</td>
                <td class="px-3 numeric-delta" data-value="{$row.submitted_delta|escape}">{$row.submitted_delta|escape}</td>

                <td class="px-3 border-left numeric" data-value="{$row.closed|escape}">{$row.closed|escape}</td>
                <td class="px-3 numeric-delta" data-value="{$row.closed_delta|escape}">{$row.closed_delta|escape}</td>

                <td class="px-3 border-left numeric-delta delta-inverse" data-value="{$row.open_req_delta|escape}">{$row.open_req_delta|escape}</td>

                <td class="px-3 border-left numeric" data-value="{$row.activeusers|escape}">{$row.activeusers|escape}</td>
                <td class="px-3 numeric-delta" data-value="{$row.activeusers_delta|escape}">{$row.activeusers_delta|escape}</td>

                <td class="px-3 border-left numeric" data-value="{$row.nondeferred|escape}">{$row.nondeferred|escape|timespan}</td>
                <td class="px-3 numeric-delta delta-inverse" data-value="{$row.nondeferred_delta|escape}">{$row.nondeferred_delta|escape|timespan}</td>
                <td class="px-3 numeric" data-value="{$row.nondeferred_stddev|escape}">{$row.nondeferred_stddev|escape|timespan}</td>
                <td class="px-3 border-left numeric" data-value="{$row.deferred|escape}">{$row.deferred|escape|timespan}</td>
                <td class="px-3 numeric-delta delta-inverse" data-value="{$row.deferred_delta|escape}">{$row.deferred_delta|escape|timespan}</td>
                <td class="px-3 numeric" data-value="{$row.deferred_stddev|escape}">{$row.deferred_stddev|escape|timespan}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}

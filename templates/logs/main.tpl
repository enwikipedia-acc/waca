{extends file="pagebase.tpl"}
{block name="content"}
    <!-- tpl:logs/main.tpl -->
    <div class="page-header">
        <h1>Log Viewer&nbsp;
            <small>See all the logs</small>
        </h1>
    </div>
    {include file="logs/form.tpl"}
    {include file="logs/pager.tpl"}
    <table class="table table-striped table-hover table-condensed table-nonfluid">
        <thead>
        <tr>
            <th>Timestamp</th>
            <th>User</th>
            <th>Action</th>
            <th>Object</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$logs item=entry name=logloop}
            <tr>
                <td>{$entry.timestamp} <em class="muted">({$entry.timestamp|relativedate})</em></td>
                <td>
                    {if $entry.userid != -1}
                        <a href='{$baseurl}/internal.php/statistics/users/detail?user={$entry.username|escape}'>
                            {$entry.username|escape}
                        </a>
                    {/if}
                    {$entry.username|escape}
                </td>
                <td>{$entry.description|escape}</td>
                <td>{$entry.objectdescription}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {include file="logs/pager.tpl"}
    <!-- /tpl:logs/main.tpl -->
{/block}
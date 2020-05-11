{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <table class="table table-bordered table-nonfluid table-hover table-sm sortable">
        <thead>
            <tr>
                <th>Username</th>
                <th>Tool user</th>
                <th><abbr title="Handles day-to-day tool administration, user access, etc.">Tool admin</abbr></th>
                <th><abbr title="Has checkuser access to data, only given to on-wiki checkusers">Checkuser</abbr></th>
                <th><abbr title="Has shell access to the servers which run the tool">Root</abbr></th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$users item="user"}
                <tr>
                    <td>
                        <a href="{$baseurl}/internal.php/statistics/users/detail?user={$user.id}">{$user.username|escape}</a>
                    </td>
                    <td {if $user.tooluser === 'Yes'}class="bg-success-light"{else}class="bg-danger-light"{/if}>{$user.tooluser}</td>
                    <td {if $user.tooladmin === 'Yes'}class="bg-success-light"{else}class="bg-danger-light"{/if}>{$user.tooladmin}</td>
                    <td {if $user.checkuser === 'Yes'}class="bg-success-light"{else}class="bg-danger-light"{/if}>{$user.checkuser}</td>
                    <td {if $user.toolroot === 'Yes'}class="bg-success-light"{else}class="bg-danger-light"{/if}>{$user.toolroot}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{/block}

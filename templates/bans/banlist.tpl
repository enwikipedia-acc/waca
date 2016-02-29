{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Ban Management
            <small>View, ban, and unban requesters
                {if $page->barrierTest('set')}
                    <a class="btn btn-primary" href="{$baseurl}/internal.php/bans/set"><i class="icon-white icon-plus"></i>&nbsp;Add new Ban</a>
                {/if}
            </small>
        </h1>
    </div>
    <h3>Active Ban List</h3>
    <table class="table table-striped">
        <thead>
        <th>Type</th>
        <th>Target</th>
        <td>{* search! *}</td>
        <th>Banned by</th>
        <th>Reason</th>
        <th>Time</th>
        <th>Expiry</th>
        {if $page->barrierTest('remove')}
            <th>Unban</th>
        {/if}
        </thead>
        <tbody>
        {foreach from=$activebans item="ban"}
            <tr>
                <td>{$ban->getType()|escape}</td>
                <td>{$ban->getTarget()|escape}</td>
                <td>
                    {if $ban->getType() == "IP"}
                        <a class="btn btn-small btn-info"
                                {* todo: update this URL *}
                           href="{$baseurl}/search.php?type=IP&amp;term={$ban->getTarget()|escape:'url'}">
                            <i class="icon-white icon-search"></i>
                            <span class="visible-desktop">&nbsp;Search</span>
                        </a>
                    {elseif $ban->getType() == "Name"}
                        <a class="btn btn-small btn-info"
                                {* todo: update this URL *}
                           href="{$baseurl}/search.php?type=Request&amp;term={$ban->getTarget()|escape:'url'}">
                            <i class="icon-white icon-search"></i>
                            <span class="visible-desktop">&nbsp;Search</span>
                        </a>
                    {elseif $ban->getType() == "EMail"}
                        <a class="btn btn-small btn-info"
                                {* todo: update this URL *}
                           href="{$baseurl}/search.php?type=email&amp;term={$ban->getTarget()|escape:'url'}">
                            <i class="icon-white icon-search"></i>
                            <span class="visible-desktop">&nbsp;Search</span>
                        </a>
                    {/if}
                </td>
                <td>{$usernames[$ban->getUser()]|escape}</td>
                <td>{$ban->getReason()|escape}</td>
                <td>{$ban->getDate()} <span class="muted">({$ban->getDate()|relativedate})</span></td>
                <td>{if $ban->getDuration() == -1}Indefinite{else}{date("Y-m-d H:i:s", $ban->getDuration())}{/if}</td>

                {if $page->barrierTest('remove')}
                    <td>
                        <a class="btn btn-success btn-small"
                           href="{$baseurl}/internal.php/bans/remove?id={$ban->getId()}">
                            <i class="icon-white icon-ok"></i><span class="visible-desktop">&nbsp;Unban</span>
                        </a>
                    </td>
                {/if}
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}
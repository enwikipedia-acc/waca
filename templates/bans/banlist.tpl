{extends file="pagebase.tpl"}
{block name="content"}
    <div class="jumbotron">
        <h1>Ban Management</h1>
        <p>View, ban, and unban requesters</p>
        {if $canSet}
            <a class="btn btn-primary" href="{$baseurl}/internal.php/bans/set"><i class="fas fa-plus"></i>&nbsp;Add new Ban</a>
        {/if}

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
        {if $canRemove}
            <th>Unban</th>
        {/if}
        </thead>
        <tbody>
        {foreach from=$activebans item="ban"}
            <tr>
                <td>{$ban->getType()|escape}</td>
                <td>{$ban->getTarget()|escape}</td>
                <td>
                    <form action="{$baseurl}/internal.php/search" method="post" class="form-compact">
                        {include file="security/csrf.tpl"}
                        <input type="hidden" name="term" value="{$ban->getTarget()|escape}" />

                        {if $ban->getType() == "IP"}
                            <input type="hidden" name="type" value="ip" />
                        {elseif $ban->getType() == "Name"}
                            <input type="hidden" name="type" value="name" />
                        {elseif $ban->getType() == "EMail"}
                            <input type="hidden" name="type" value="email" />
                        {/if}

                        <button type="submit" class="btn btn-sm btn-info">
                            <i class="fas fa-search"></i>
                            <span class="d-none d-lg-block">&nbsp;Search</span>
                        </button>
                    </form>
                </td>
                <td>{$usernames[$ban->getUser()]|escape}</td>
                <td>{$ban->getReason()|escape}</td>
                <td>{$ban->getDate()} <span class="text-muted">({$ban->getDate()|relativedate})</span></td>
                <td>{if $ban->getDuration() == -1}Indefinite{else}{date("Y-m-d H:i:s", $ban->getDuration())}{/if}</td>

                {if $canRemove}
                    <td>
                        <a class="btn btn-success btn-sm"
                           href="{$baseurl}/internal.php/bans/remove?id={$ban->getId()}">
                            <i class="fas fa-check-circle"></i><span class="d-none d-lg-block">&nbsp;Unban</span>
                        </a>
                    </td>
                {/if}
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}

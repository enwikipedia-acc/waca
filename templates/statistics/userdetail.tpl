{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <div class="row-fluid">
        <div class="span6">
            <h3>Detail report for user: {$user->getUsername()|escape}</h3>
            <div class="container-fluid">
                <div class="row-fluid">
                    <div class="span4"><strong>User ID:</strong></div>
                    <div class="span8">{$user->getId()}</div>
                </div>
                <div class="row-fluid">
                    <div class="span4"><strong>User Level</strong></div>
                    <div class="span8">{$user->getStatus()}</div>
                </div>
                <div class="row-fluid">
                    <div class="span4"><strong>User on-wiki name:</strong></div>
                    <div class="span8">{$user->getOnWikiName()|escape}
                        {if $user->isOAuthLinked()}
                            <span class="label {if $user->getOnWikiName() == "##OAUTH##"}label-important{else}label-success{/if}">OAuth</span>
                        {/if}
                    </div>
                </div>
                {if $user->getConfirmationDiff() != 0}
                <div class="row-fluid">
                    <div class="span4"><strong>Confirmation diff:</strong></div>
                    <div class="span8"><a href="{$mediawikiScriptPath}?diff={$user->getConfirmationDiff()|escape}">{$user->getConfirmationDiff()|escape}</a></div>
                </div>
                {/if}
                <div class="row-fluid">
                    <div class="span4"><strong>User last active:</strong></div>
                    <div class="span8">
                        {if $user->getLastActive() == "0000-00-00 00:00:00"}
                            User has never used the interface
                        {else}
                            {$user->getLastActive()} <span class="muted">({$user->getLastActive()|relativedate})</span>
                        {/if}
                    </div>
                </div>
            </div>

            {include file="usermanagement/buttons.tpl"}

        </div>

        <div class="span6">
            <h3>Summary of user activity:</h3>
            <table class="table table-striped table-condensed">
                {foreach from=$activity item="row"}
                    <tr>
                        <td>{$row.action|escape}</td>
                        <td>{$row.count|escape}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span6">
            <h3>Users created</h3>
            <ol>
                {foreach from=$created item="user"}
                    <li>
                        <a href="{$mediawikiScriptPath}?title=User:{$user.name|escape:'url'}">{$user.name|escape}</a>
                        (
                        <a href="{$mediawikiScriptPath}?title=User_talk:{$user.name|escape:'url'}">talk</a> -
                        <a href="{$mediawikiScriptPath}?title=Special:Contributions/{$user.name|escape:'url'}">contribs</a>
                        -
                        <a href="{$baseurl}/internal.php/viewRequest?id={$user.id}">zoom</a>
                        ) at {$user.time}
                    </li>
                {/foreach}
            </ol>
        </div>

        <div class="span6">
            <h3>Users not created</h3>
            <ol>
                {foreach from=$notcreated item="user"}
                    <li>
                        <a href="{$mediawikiScriptPath}?title=User:{$user.name|escape:'url'}">{$user.name|escape}</a>
                        (
                        <a href="{$mediawikiScriptPath}?title=User_talk:{$user.name|escape:'url'}">talk</a> -
                        <a href="{$mediawikiScriptPath}?title=Special:Contributions/{$user.name|escape:'url'}">contribs</a>
                        -
                        <a href="{$baseurl}/internal.php/viewRequest?id={$user.id}">zoom</a>
                        ) at {$user.time}
                    </li>
                {/foreach}
            </ol>

        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <h3>Account log</h3>

            {include file="logs/datatable.tpl" showComments=true logs=$accountlog}
        </div>
    </div>
{/block}
